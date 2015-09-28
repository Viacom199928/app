<?php

/**
 * Class WallNotificationsController
 *
 * Render Notifications in top-right corner of Wikia interface
 */
class WallNotificationsController extends WikiaController {

	const NOTIFICATION_TITLE_LIMIT = 48;

	public function Index() {
		global $wgUser, $wgEnableWallExt, $wgEnableForumExt;

		wfProfileIn( __METHOD__ );
		$loggedIn = $wgUser->isLoggedIn();
		$suppressWallNotifications = $this->areNotificationsSuppressedByExtensions();

		if ( $loggedIn && !$suppressWallNotifications ) {
			OasisController::addSkinAssetGroup( 'wall_notifications_js' );
			$this->response->addAsset( 'extensions/wikia/WallNotifications/styles/WallNotifications.scss' );
			$this->response->setVal( 'prehide', ( empty( $wgEnableWallExt ) && empty( $wgEnableForumExt ) ) );
		}

		$this->response->setVal( 'loggedIn', $loggedIn );
		$this->response->setVal( 'suppressWallNotifications', $suppressWallNotifications );
		wfProfileOut( __METHOD__ );
	}

	public function Update() {
		global $wgUser, $wgEnableWallExt, $wgEnableForumExt;

		wfProfileIn( __METHOD__ );

		$this->response->setVal( 'alwaysGrouped', empty( $wgEnableWallExt ) && empty( $wgEnableForumExt ) );
		$this->response->setVal( 'notificationKey', $this->request->getVal( 'notificationKey' ) );

		$notificationCounts = $this->request->getVal( 'notificationCounts' );
		$this->response->setVal( 'notificationCounts', $notificationCounts );

		$unreadCount = $this->request->getVal( 'count' );
		$this->response->setVal( 'count', $unreadCount );

		$this->response->setVal( 'user', $wgUser );

		wfProfileOut( __METHOD__ );
	}

	public function UpdateWiki() {
		global $wgUser;

		wfProfileIn( __METHOD__ );

		$all = $this->request->getVal( 'notifications' );

		$this->response->setVal( 'user', $wgUser );
		$this->response->setVal( 'unread', $all['unread'] );
		$this->response->setVal( 'read', $all['read'] );

		wfProfileOut( __METHOD__ );
	}

	public function NotificationAdmin() {
		$notify = $this->request->getVal( 'notify' );
		$data = $notify['grouped'][0]->data;

		$authoruser = User::newFromId( $data->user_removing_id );
		$walluser = User::newFromId( $data->user_wallowner_id );

		if ( $authoruser instanceof User ) {
			if ( $authoruser->getId() > 0 ) {
				$displayname = $authoruser->getName();
			} else {
				$displayname = wfMessage( 'oasis-anon-user' )->text();
			}
		} else {
			// annon
			$displayname = wfMessage( 'oasis-anon-user' )->text();
		}

		$wall_displayname = $walluser->getName();

		$authors = [];
		$authors[] = [
			'displayname' => $displayname,
			'username' => $displayname
		];

		if ( $data->type == 'OWNER' ) {
			if ( !$data->is_reply ) {
				$msg = wfMessage( 'wn-owner-thread-deleted' )->text();
			} else {
				$msg = wfMessage( 'wn-owner-reply-deleted' )->text();
			}
		} else {
			if ( !$data->is_reply ) {
				$msg = wfMessage( 'wn-admin-thread-deleted', [ $wall_displayname ] )->text();
			} else {
				$msg = wfMessage( 'wn-admin-reply-deleted', [ $wall_displayname ] )->text();
			}
		}

		$this->response->setVal( 'url', $this->fixNotificationURL( $data->url ) );
		$this->response->setVal( 'msg', $msg );
		$this->response->setVal( 'authors', $authors );
		$this->response->setVal( 'title', $data->title );
		$this->response->setVal( 'iso_timestamp',  wfTimestamp( TS_ISO_8601, $data->timestamp ) );
	}

	private function areNotificationsSuppressedByExtensions() {
		global $wgUser, $wgAtCreateNewWikiPage;

		$suppressed = $wgAtCreateNewWikiPage || !$wgUser->isAllowed( 'read' );
		return !empty( $suppressed );
	}

	private function fixNotificationURL( $url ) {
		global $wgStagingList;
		$hostOn = getHostPrefix();

		$hosts = $wgStagingList;
		foreach ( $hosts as $host ) {
			$prefix = 'http://' . $host . '.';
			if ( strpos( $url, $prefix )  !== false ) {
				if ( empty( $hostOn ) ) {
					return str_replace( $prefix, 'http://', $url );
				} else {
					return str_replace( $prefix, 'http://' . $hostOn . '.', $url );
				}
			}
		}

		if ( !empty( $hostOn ) ) {
			return str_replace( 'http://', 'http://' . $hostOn . '.', $url );
		}

		return $url;
	}

	public function Notification() {
		$wg = F::app()->wg;

		$notify = $this->request->getVal( 'notify' );
		if ( empty( $notify['grouped'][0] ) ) {
			// Do not try to render notifications missing this data
			return;
		}
		/** @var WallNotificationEntity $firstEntity */
		$firstEntity = $notify['grouped'][0];

		$data = $firstEntity->data;
		if ( isset( $data->type ) && $data->type === 'ADMIN' ) {
			$this->forward( __CLASS__, 'NotificationAdmin' );
			return;
		}

		if ( isset( $data->type ) && $data->type === 'OWNER' ) {
			$this->forward( __CLASS__, 'NotificationAdmin' );
			return;
		}
		$authors = [];
		foreach ( $notify['grouped'] as $notify_entity ) {
			$authors[] = [
				'displayname' => $notify_entity->data->msg_author_displayname,
				'username' => $notify_entity->data->msg_author_username
			];
		}

		// 1 = 1 user,
		// 2 = 2 users,
		// 3 = more than 2 users

		$userCount = 1;
		$authorsCount = count( $authors );
		if ( $authorsCount == 2 ) {
			$userCount = 2;
		} elseif ( $authorsCount > 2 ) {
			$userCount = 3;
		}

		$msg = "";
		wfRunHooks( 'NotificationGetNotificationMessage', [
			&$this, &$msg, $firstEntity->isMain(), $data, $authors, $userCount,  $wg->User->getName()
		] );

		if ( empty( $msg ) ) {
			$msg = $this->getNotificationMessage(
				$firstEntity->isMain(),
				$data,
				$authors,
				$userCount,
				$wg->User->getName()
			);
		}

		$unread = $this->request->getVal( 'unread' );
		$this->response->setVal( 'unread', $unread );
		if ( !$unread ) {
			$authors = array_slice( $authors, 0, 1 );
		}

		$this->response->setVal( 'msg', $msg );

		// The instances of `WallNotificationEntity` in the `$notify['grouped']` array are sorted in reverse
		// chronological order. We want the url to point to the oldest unread item (which is the last element in the
		// array) instead of the most recent so that they start reading where the left off. See bugid 64560.
		$oldestEntity = end( $notify['grouped'] );
		$url = empty( $oldestEntity->data->url ) ? '' :  $oldestEntity->data->url;

		$this->response->setVal( 'url', $this->fixNotificationURL( $url ) );
		$this->response->setVal( 'authors', array_reverse( $authors ) );
		$this->response->setVal( 'title', $data->thread_title );
		$this->response->setVal( 'iso_timestamp',  wfTimestamp( TS_ISO_8601, $data->timestamp ) );

		if ( $unread && $data->notifyeveryone ) {
			$this->response->getView()->setTemplate( 'WallNotificationsController', 'NotifyEveryone' );
		}
	}

	private function getNotificationMessage( $isMain, $data, $authors, $userCount, $myName ) {
		$params = [];
		if ( !$isMain ) {
			$params[] = $this->getDisplayname( $data->msg_author_displayname );

			if ( $userCount == 2 ) {
				$params['$1'] = $this->getDisplayname( $authors[1]['displayname'] );
			}

			$reply_by = 'other'; // who replied?
							   // you = same as person receiving notification
							   // self = same as person who wrote original message (parent)
							   // other = someone else

			if ( $data->parent_username == $myName ) {
				$reply_by = 'you';
			} elseif ( in_array( $data->parent_username, $authors ) ) {
				$reply_by = 'self';
			} else $params['$' . ( count( $params ) + 1 )] = $this->getDisplayname( $data->parent_displayname );

			$whos_wall = 'a'; // on who's wall was the message written?
								   // your  = on message author's wall
								   // other = on someone else's wall
								   // a     = the person was already mentioned (either author of msg or thread)

			if ( $data->wall_username == $myName ) {
				$whos_wall = 'your';
			} elseif ( $data->wall_username != $data->parent_username && !in_array( $data->wall_username, $authors ) ) {
				$whos_wall = 'other';
				$params['$' . ( count( $params ) + 1 )] = $this->getDisplayname( $data->wall_displayname );
			}

			$msgid = "wn-user$userCount-reply-$reply_by-$whos_wall-wall";
		} else {
			if ( $data->wall_username == $myName ) {
				$msgid = 'wn-newmsg-onmywall';
				$params['$' . ( count( $params ) + 1 )] = $this->getDisplayname( $data->msg_author_displayname );
			} else if ( $data->msg_author_username != $myName ) {
				$msgid = 'wn-newmsg-on-followed-wall';
				$params['$' . ( count( $params ) + 1 )] = $this->getDisplayname( $data->msg_author_displayname );
				$params['$' . ( count( $params ) + 1 )] = $this->getDisplayname( $data->wall_displayname );
			} else {
				$msgid = 'wn-newmsg';
				$params['$' . ( count( $params ) + 1 )] = $this->getDisplayname( $data->wall_displayname );
			}
		}
		$msg = wfMessage( $msgid, $params )->text();
		return $msg;
	}

	public function getDisplayname( $username ) {
		if ( User::isIP( $username ) ) {
			return wfMessage( 'oasis-anon-user' )->text();
		}
		return $username;
	}

	public function checkTopic() {
		// force json format
		$this->getResponse()->setFormat( 'json' );

		$result = false;

		$topic = $this->getRequest()->getVal( 'query' );
		if ( !empty( $topic ) ) {
			/** @var $title title */
			$title = Title::newFromText( $topic );

			if ( $title instanceof Title ) {
				$result = (bool) $title->exists();
			}
		}

		$this->response->setVal( 'exists' , $result );
	}

	public function getEntityData() {
		$this->response->setFormat( WikiaResponse::FORMAT_JSON );

		$revId = $this->getVal( 'revId' );
		$useMasterDB = $this->getVal( 'useMasterDB', false );

		$wn = new WallNotificationEntity();
		if ( $wn->loadDataFromRevId( $revId, $useMasterDB ) ) {
			$this->response->setData( [
				'data' => $wn->data,
				'parentTitleDbKey' => $wn->parentTitleDbKey,
				'msgText' => $wn->msgText,
				'threadTitleFull' => $wn->threadTitleFull,
				'status' => 'ok',
			] );
		} else {
			$this->response->setData( [
				'status' => 'error'
			] );
		}
	}
}

