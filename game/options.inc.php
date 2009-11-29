<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* generated at Mon, 06 Aug 2007 10:40:53 +0000
*/
define('ACTIVITY_POINTS_PER_POST', 5);
define('ACTIVITY_POINTS_PER_THREAD', 10);
define('ATTACHMENT_ENABLE_CONTENT_PREVIEW', 1);
define('ATTACHMENT_ENABLE_THUMBNAILS', 1);
define('ATTACHMENT_THUMBNAIL_ADD_SOURCE_INFO', 1);
define('ATTACHMENT_THUMBNAIL_HEIGHT', 160);
define('ATTACHMENT_THUMBNAIL_WIDTH', 160);
define('BOARD_DEFAULT_DAYS_PRUNE', '1000');
define('BOARD_DEFAULT_SORT_FIELD', 'lastPostTime');
define('BOARD_DEFAULT_SORT_ORDER', 'DESC');
define('BOARD_ENABLE_DELETED_THREAD_NOTE', 0);
define('BOARD_ENABLE_MODERATORS', 1);
define('BOARD_ENABLE_ONLINE_LIST', 1);
define('BOARD_ENABLE_STATS', 1);
define('BOARD_LIST_DEPTH', 2);
define('BOARD_LIST_ENABLE_LAST_POST', 1);
define('BOARD_LIST_ENABLE_MODERATORS', 0);
define('BOARD_LIST_ENABLE_ONLINE_LIST', 1);
define('BOARD_LIST_ENABLE_SUB_BOARDS', 1);
define('BOARD_THREADS_ENABLE_MESSAGE_PREVIEW', 1);
define('BOARD_THREADS_ENABLE_OWN_POSTS', 1);
define('BOARD_THREADS_PER_PAGE', 20);
define('BOARD_THREADS_REPLIES_HOT', 20);
define('BOARD_THREADS_VIEWS_HOT', 100);
define('CENSORED_WORDS', '');
define('COOKIE_DOMAIN', '');
define('COOKIE_PATH', '/');
define('COOKIE_PREFIX', 'wcf_');
define('ENABLE_CENSORSHIP', 0);
define('ENABLE_DAYLIGHT_SAVING_TIME', 0);
define('ENCRYPTION_ENABLE_SALTING', 1);
define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', 1);
define('ENCRYPTION_METHOD', 'sha1');
define('ENCRYPTION_SALT_POSITION', 'before');
define('HTTP_CONTENT_TYPE_XHTML', 1);
$_SERVER['HTTP_ACCEPT'] = str_replace('application/xhtml+xml', 'platzhalter', $_SERVER['HTTP_ACCEPT']);
define('HTTP_ENABLE_GZIP', 1);
define('HTTP_ENABLE_NO_CACHE_HEADERS', 0);
define('HTTP_GZIP_LEVEL', 1);
define('INDEX_ENABLE_ONLINE_LIST', 1);
define('INDEX_ENABLE_STATS', 1);
define('INDEX_ENABLE_USERS_ONLINE_LEGEND', 0);
define('INSTALL_DATE', 0);
define('LEGAL_NOTICE_ADDITIONAL_TEXT', '');
define('LEGAL_NOTICE_ADDITIONAL_TEXT_ENABLE_HTML', 0);
define('LEGAL_NOTICE_ADDRESS', '');
define('LEGAL_NOTICE_EMAIL_ADDRESS', '');
define('LEGAL_NOTICE_FAX', '');
define('LEGAL_NOTICE_PHONE', '');
define('LEGAL_NOTICE_REGISTER', '');
define('LEGAL_NOTICE_REPRESENTATIVE', '');
define('LEGAL_NOTICE_URL', '');
define('LEGAL_NOTICE_USE_URL', 0);
define('LEGAL_NOTICE_VAT_ID', '');
define('LOGIN_USE_CAPTCHA', 0);
define('LOST_PASSWORD_USE_CAPTCHA', 0);
define('MAIL_ADMIN_ADDRESS', '');
define('MAIL_DEBUG_LOGFILE_PATH', '');
define('MAIL_FROM_ADDRESS', '');
define('MAIL_FROM_NAME', '');
define('MAIL_SEND_METHOD', 'php');
define('MAIL_SIGNATURE', '');
define('MAIL_SMTP_HOST', '');
define('MAIL_SMTP_PASSWORD', '');
define('MAIL_SMTP_PORT', 25);
define('MAIL_SMTP_USER', '');
define('MAIL_USE_F_PARAM', 0);
define('MEMBERS_LIST_COLUMNS', 'username,avatar,email,homepage,registrationDate');
define('MEMBERS_LIST_DEFAULT_SORT_FIELD', 'username');
define('MEMBERS_LIST_DEFAULT_SORT_ORDER', 'DESC');
define('MEMBERS_LIST_USERS_PER_PAGE', 30);
define('MESSAGE_FORM_DEFAULT_ENABLE_BBCODES', 1);
define('MESSAGE_FORM_DEFAULT_ENABLE_HTML', 0);
define('MESSAGE_FORM_DEFAULT_ENABLE_SMILIES', 1);
define('MESSAGE_FORM_DEFAULT_PARSE_URL', 1);
define('META_DESCRIPTION', '');
define('META_KEYWORDS', '');
define('OFFLINE', 0);
define('OFFLINE_MESSAGE', '');
define('OFFLINE_MESSAGE_ALLOW_HTML', 0);
define('PAGE_DESCRIPTION', '');
define('PAGE_TITLE', 'War of Times');
define('PAGE_URL', '');
define('PAGE_URLS', '');
define('PM_DEFAULT_SORT_FIELD', 'time');
define('PM_DEFAULT_SORT_ORDER', 'DESC');
define('PM_MESSAGES_PER_PAGE', 10);
define('PM_SHOW_USER_OPTIONS', 'location,occupation,homepage,email,icq,skype');
define('POST_ADD_USE_CAPTCHA', 1);
define('POST_EDIT_HIDE_EDIT_NOTE_PERIOD', 5);
define('POST_NOTIFICATION_SEND_FULL_MESSAGE', 1);
define('PROFILE_MAIL_USE_CAPTCHA', 1);
define('PROFILE_SHOW_OLD_USERNAME', 182);
define('REGISTER_ACTIVATION_METHOD', '1');
define('REGISTER_ADMIN_NOTIFICATION', 0);
define('REGISTER_DISABLED', 0);
define('REGISTER_ENABLE_DISCLAIMER', 1);
define('REGISTER_ENABLE_PASSWORD_SECURITY_CHECK', 0);
define('REGISTER_FORBIDDEN_EMAILS', '');
define('REGISTER_FORBIDDEN_USERNAMES', '');
define('REGISTER_PASSWORD_MIN_LENGTH', 8);
define('REGISTER_PASSWORD_MUST_CONTAIN_DIGIT', 1);
define('REGISTER_PASSWORD_MUST_CONTAIN_LOWER_CASE', 1);
define('REGISTER_PASSWORD_MUST_CONTAIN_SPECIAL_CHAR', 1);
define('REGISTER_PASSWORD_MUST_CONTAIN_UPPER_CASE', 1);
define('REGISTER_UNIQUE_IP_ADDRESS', 3600);
define('REGISTER_USERNAME_MAX_LENGTH', 25);
define('REGISTER_USERNAME_MIN_LENGTH', 3);
define('REGISTER_USE_CAPTCHA', 1);
define('REPLY_OLD_THREAD_WARNING', 365);
define('REPLY_SHOW_POSTS_MAX', 10);
define('SEARCH_RESULTS_PER_PAGE', 20);
define('SEARCH_RESULT_GROUP_BY_BOARD', 1);
define('SEARCH_USE_CAPTCHA', 0);
define('SESSION_TIMEOUT', 3600);
define('SHOW_CLOCK', 1);
define('SHOW_VERSION_NUMBER', 1);
define('THREAD_EMPTY_RECYCLE_BIN_CYCLE', 30);
define('THREAD_ENABLE_AVATAR', 1);
define('THREAD_ENABLE_DELETED_POST_NOTE', 1);
define('THREAD_ENABLE_ONLINE_LIST', 1);
define('THREAD_ENABLE_ONLINE_STATUS', 1);
define('THREAD_ENABLE_RANK', 1);
define('THREAD_ENABLE_RATING', 1);
define('THREAD_ENABLE_RECYCLE_BIN', 1);
define('THREAD_ENABLE_REGISTRATION_DATE', 0);
define('THREAD_ENABLE_SIMILAR_THREADS', 1);
define('THREAD_ENABLE_THREAD_AUTHOR', 1);
define('THREAD_ENABLE_USER_POSTS', 1);
define('THREAD_MIN_RATINGS', 1);
define('THREAD_POSTS_PER_PAGE', 20);
define('THREAD_SHOW_USER_OPTIONS', 'location,occupation,homepage,email,icq,skype');
define('TIMEZONE', '1');
define('USERS_ONLINE_PAGE_REFRESH', 0);
define('USERS_ONLINE_RECORD', 5);
define('USERS_ONLINE_RECORD_TIME', 1185762483);
define('USERS_ONLINE_SHOW_GUESTS', 1);
define('USERS_ONLINE_SHOW_ROBOTS', 1);
define('USER_ONLINE_TIMEOUT', 900);
define('VISIT_TIME_FRAME', 604800);
define('WYSIWYG_EDITOR_HEIGHT', 200);
define('WYSIWYG_EDITOR_MODE', 1);
?>
