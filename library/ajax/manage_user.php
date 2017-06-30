<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata;

$mode = (string)$this->request['mode'];
$user_id = $this->request['user_id'];

switch ($mode) {
    case 'delete_profile':

        if ($userdata['user_id'] == $user_id) {
            $this->ajax_die(trans('messages.USER_DELETE_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(trans('messages.USER_DELETE_CONFIRM'));
        }

        if ($user_id != BOT_UID) {
            delete_user_sessions($user_id);
            user_delete($user_id);

            $this->response['info'] = trans('messages.USER_DELETED');
        } else {
            $this->ajax_die(trans('messages.USER_DELETE_CSV'));
        }

        break;

    case 'delete_topics':

        if (empty($this->request['confirmed']) && $userdata['user_id'] == $user_id) {
            $this->prompt_for_confirm(trans('messages.DELETE_USER_POSTS_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(trans('messages.DELETE_USER_ALL_POSTS_CONFIRM'));
        }

        if (IS_ADMIN) {
            $user_topics = OLD_DB()->fetch_rowset('SELECT topic_id FROM ' . BB_TOPICS . " WHERE topic_poster = $user_id", 'topic_id');
            $deleted_topics = topic_delete($user_topics);
            $deleted_posts = post_delete('user', $user_id);

            $this->response['info'] = trans('messages.USER_DELETED_POSTS');
        } else {
            $this->ajax_die(trans('messages.NOT_ADMIN'));
        }

        break;

    case 'delete_message':

        if (empty($this->request['confirmed']) && $userdata['user_id'] == $user_id) {
            $this->prompt_for_confirm(trans('messages.DELETE_USER_POSTS_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(trans('messages.DELETE_USER_POSTS_CONFIRM'));
        }

        if (IS_ADMIN) {
            post_delete('user', $user_id);

            $this->response['info'] = trans('messages.USER_DELETED_POSTS');
        } else {
            $this->ajax_die(trans('messages.NOT_ADMIN'));
        }

        break;

    case 'user_activate':

        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(trans('messages.DEACTIVATE_CONFIRM'));
        }

        OLD_DB()->query('UPDATE ' . BB_USERS . " SET user_active = '1' WHERE user_id = " . $user_id);

        $this->response['info'] = trans('messages.USER_ACTIVATE_ON');

        break;

    case 'user_deactivate':

        if ($userdata['user_id'] == $user_id) {
            $this->ajax_die(trans('messages.USER_DEACTIVATE_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(trans('messages.ACTIVATE_CONFIRM'));
        }

        OLD_DB()->query('UPDATE ' . BB_USERS . " SET user_active = '0' WHERE user_id = " . $user_id);
        delete_user_sessions($user_id);

        $this->response['info'] = trans('messages.USER_ACTIVATE_OFF');

        break;
}

$this->response['mode'] = $mode;
$this->response['url'] = html_entity_decode(make_url('/') . PROFILE_URL . $user_id);
