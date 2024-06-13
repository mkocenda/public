<?php

namespace App\Model;

class Types
{

	const INFO = 'info';
	const SUCCESS = 'success';
	const WARNING = 'warning';
	const DANGER = 'danger';

	const MB_OK = 'btn btn-success';
	const MB_CANCEL = 'btn btn-warning';
	const MB_DELETE = 'btn btn-danger';

	const LOG_INFO = 'info';
	const LOG_WARNING = 'warning';
	const LOG_DANGER = 'danger';
	const LOG_ERROR = 'error';

	const PLANNED = 1;
	const RUNNING = 2;
	const DONE = 3;

	const DOSAGE_NA = '0-0-0';
	const DOSAGE_MO = '1-0-0';
	const DOSAGE_LA = '0-1-0';
	const DOSAGE_NI = '0-0-1';
	const DOSAGE_MOLA = '1-1-0';
	const DOSAGE_MONI = '1-0-1';
	const DOSAGE_LANI = '0-1-1';
	const DOSAGE_ALL = '1-1-1';
	
	const read = 0;
	const unread = 1;
	const DOSAGE = array('DOSAGE_ALL' => self::DOSAGE_ALL,
		'DOSAGE_MO' => self::DOSAGE_MO,
		'DOSAGE_LA' => self::DOSAGE_LA,
		'DOSAGE_NI' => self::DOSAGE_NI,
		'DOSAGE_MOLA' => self::DOSAGE_MOLA,
		'DOSAGE_MONI' => self::DOSAGE_MONI,
		'DOSAGE_LANI' => self::DOSAGE_LANI,
		'DOSAGE_NA' => self::DOSAGE_NA);

	const C_GENERAL_API_ERROR = 500;
	const T_GENERAL_API_ERROR = 'api encountered an error, cannot continue';

	const C_WRONG_API_KEY = 400;
	const T_WRONG_API_KEY = 'invalid api key supplied';

	const C_INVALID_API_KEY = 401;
	const T_INVALID_API_KEY = 'invalid api key supplied';

	const C_INVALID_CONTENT_TYPE = 400;
	const T_INVALID_CONTENT_TYPE = 'Content-Type header must be application/json';

	const C_INVALID_ACCEPT = 400;
	const T_INVALID_ACCEPT = 'Accept header must be application/json';

    const ENABLED = 1;
    const DISABLED = 0;
	
	const LOCK = 1;
	const UNLOCK = 0;
	
	const HASH_OK = 'VALID';
	const HASH_EXP = 'EXPIRED';
}