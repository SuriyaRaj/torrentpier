<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Lock tables
OLD_DB()->lock(array(
    BB_TOPICS . ' t',
    BUF_TOPIC_VIEW . ' buf',
));

// Flash buffered records
OLD_DB()->query('
	UPDATE
		' . BB_TOPICS . ' t,
		' . BUF_TOPIC_VIEW . ' buf
	SET
		t.topic_views = t.topic_views + buf.topic_views
	WHERE
		t.topic_id = buf.topic_id
');

// Delete buffered records
OLD_DB()->query('DELETE buf FROM ' . BUF_TOPIC_VIEW . ' buf');

// Unlock tables
OLD_DB()->unlock();
