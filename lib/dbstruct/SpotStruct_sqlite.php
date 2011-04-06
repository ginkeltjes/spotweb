<?php
require_once "lib/dbstruct/SpotStruct_abs.php";

class SpotStruct_sqlite extends SpotStruct_abs {
	
	function createDatabase() {
		$q = $this->_dbcon->arrayQuery("PRAGMA table_info(spots)");
		if (empty($q)) {
			# spots
			$this->_dbcon->rawExec("CREATE TABLE spots(id INTEGER PRIMARY KEY ASC, 
											messageid VARCHAR(128),
											spotid INTEGER,
											category INTEGER, 
											subcat INTEGER,
											poster VARCHAR(128),
											groupname VARCHAR(128),
											subcata VARCHAR(64),
											subcatb VARCHAR(64),
											subcatc VARCHAR(64),
											subcatd VARCHAR(64),
											title VARCHAR(128),
											tag VARCHAR(128),
											stamp INTEGER,
											reversestamp INTEGER DEFAULT 0,
											filesize BIGINT DEFAULT 0,
											moderated BOOLEAN DEFAULT FALSE);");
			$this->_dbcon->rawExec("CREATE INDEX idx_spots_1 ON spots(id, category, subcata, subcatd, stamp DESC)");
			$this->_dbcon->rawExec("CREATE INDEX idx_spots_2 ON spots(id, category, subcatd, stamp DESC)");
			$this->_dbcon->rawExec("CREATE INDEX idx_spots_3 ON spots(messageid)");
			$this->_dbcon->rawExec("CREATE INDEX idx_spots_4 ON spots(stamp);");
			$this->_dbcon->rawExec("CREATE INDEX idx_spots_5 ON spots(poster);");
			$this->_dbcon->rawExec("CREATE INDEX idx_spots_6 ON spots(reversestamp);");

			# spotsfull table
			$this->_dbcon->rawExec("CREATE TABLE spotsfull(id INTEGER PRIMARY KEY, 
										messageid varchar(128),
										userid varchar(32),
										verified BOOLEAN,
										usersignature TEXT,
										userkey TEXT,
										xmlsignature TEXT,
										fullxml TEXT,
										filesize BIGINT);");										

			# create indices
			$this->_dbcon->rawExec("CREATE UNIQUE INDEX idx_spotsfull_1 ON spotsfull(messageid, userid)");
			$this->_dbcon->rawExec("CREATE INDEX idx_spotsfull_2 ON spotsfull(userid);");

			# NNTP table
			$this->_dbcon->rawExec("CREATE TABLE nntp(server TEXT PRIMARY KEY,
										maxarticleid INTEGER UNIQUE,
										nowrunning INTEGER DEFAULT 0,
										lastrun INTEGER DEFAULT 0);");

			# commentsxover table
			$this->_dbcon->rawExec("CREATE TABLE commentsxover(id INTEGER PRIMARY KEY ASC,
										   messageid VARCHAR(128),
										   nntpref VARCHAR(128));");
			$this->_dbcon->rawExec("CREATE INDEX idx_commentsxover_1 ON commentsxover(nntpref, messageid)");
			$this->_dbcon->rawExec("CREATE UNIQUE INDEX idx_commentsxover_2 ON commentsxover(messageid)");
			
			# downloadlist table
			$this->_dbcon->rawExec("CREATE TABLE downloadlist(id INTEGER PRIMARY KEY ASC,
										   messageid VARCHAR(128),
										   stamp INTEGER);");
			$this->_dbcon->rawExec("CREATE INDEX idx_downloadlist_1 ON downloadlist(messageid)");
			
			# watchlist table
			$this->_dbcon->rawExec("CREATE TABLE watchlist(id INTEGER PRIMARY KEY, 
												   messageid VARCHAR(128),
												   dateadded INTEGER,
												   comment TEXT);");
			$this->_dbcon->rawExec("CREATE UNIQUE INDEX idx_watchlist_1 ON watchlist(messageid)");
			# Wishlist
			$this->_dbcon->rawExec("CREATE TABLE wishlist(id INTEGER PRIMARY KEY AUTO_INCREMENT, searchstring varchar(128))");
		} # if
	} # createDatabase
	
	/* controleert of een index bestaat */
	function indexExists($tablename, $idxname) {
		$q = $this->_dbcon->arrayQuery("PRAGMA index_info(" . $idxname . ")");
		return !empty($q);
	} # indexExists

	/* controleert of een column bestaat */
	function columnExists($tablename, $colname) {
		$q = $this->_dbcon->arrayQuery("PRAGMA table_info(" . $tablename . ")");
		
		$foundCol = false;
		foreach($q as $row) {
			if ($row['name'] == $colname) {
				$foundCol = true;
				break;
			} # if
		} # foreach
		
		return $foundCol;
	} # columnExists
	

	/* Add an index, kijkt eerst wel of deze index al bestaat */
	function addIndex($idxname, $idxType, $tablename, $colList) {
		if (!$this->indexExists($tablename, $idxname)) {
			$this->_dbcon->rawExec("CREATE INDEX " . $idxname . " ON " . $tablename . "(" . $colList . ");");
		} # if
	} # addIndex

	/* dropt een index als deze bestaat */
	function dropIndex($idxname, $tablename) {
		if ($this->indexExists($tablename, $idxname)) {
			$this->_dbcon->rawExec("DROP INDEX " . $idxname);
		} # if
	} # dropIndex
	
	/* voegt een column toe, kijkt wel eerst of deze nog niet bestaat */
	function addColumn($colName, $tablename, $colDef) {
		if (!$this->columnExists($tablename, $colName)) {
			$this->_dbcon->rawExec("ALTER TABLE " . $tablename . " ADD COLUMN " . $colName . " " . $coldef);
		} # if
	} # addColumn
	
	/* dropt een kolom (mits db dit ondersteunt) */
	function dropColumn($colName, $tablename) {
		throw new Exception("Dropping of columns is not supported in sqlite");
	} # dropColumn
	
	
} # class
