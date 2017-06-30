<?php

/*©lpgl*************************************************************************
*                                                                              *
* This file is part of FRIEND UNIFYING PLATFORM.                               *
*                                                                              *
* This program is free software: you can redistribute it and/or modify         *
* it under the terms of the GNU Lesser General Public License as published by  *
* the Free Software Foundation, either version 3 of the License, or            *
* (at your option) any later version.                                          *
*                                                                              *
* This program is distributed in the hope that it will be useful,              *
* but WITHOUT ANY WARRANTY; without even the implied warranty of               *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the                 *
* GNU Affero General Public License for more details.                          *
*                                                                              *
* You should have received a copy of the GNU Lesser General Public License     *
* along with this program.  If not, see <http://www.gnu.org/licenses/>.        *
*                                                                              *
*****************************************************************************©*/


// Intermediary module to abstract some system stuff!


include_once( 'php/friend.php' );
include_once( 'php/classes/file.php' );

if( !isset( $User ) || ( $User && ( !isset( $User->ID ) || !$User->ID ) ) || !is_object( $User ) )
{
	die( 'fail<!--separate-->{"response":"user did not authenticate."}' );
}

// We might come here by mistage (direct calling of file by phpfs)
if( $args->module && $args->module != 'system' && $args->module != '(null)' )
{
	if( file_exists( $f = ( 'modules/' . $args->module . '/module.php' ) ) )
	{
		include( $f );
	}
	die( 'fail<!--separate-->' . $args->module );
}

// Get user level
if( $level = $SqlDatabase->FetchObject( '
	SELECT g.Name FROM FUserGroup g, FUserToGroup ug 
	WHERE 
		g.Type = \'Level\' AND 
		ug.UserID=\'' . $User->ID . '\' AND 
		ug.UserGroupID = g.ID
' ) )
{
	$level = $level->Name;
}
else $level = false;

function GetFilesystemByArgs( $args )
{
	global $SqlDatabase, $User;
	$identifier = false;
	
	if( isset( $args->fileInfo ) )
	{
		if( isset( $args->fileInfo->ID ) )
		{
			$identifier = 'f.ID=\'' . intval( $args->fileInfo->ID, 10 ) . '\'';
		}
		else
		{
			$identifier = 'f.Name=\'' . mysqli_real_escape_string( $SqlDatabase->_link, reset( explode( ':', $args->fileInfo->Path ) ) )  . '\'';
		}
	}
	else if( isset( $args->path ) )
	{
		$identifier = 'f.Name=\'' . mysqli_real_escape_string( $SqlDatabase->_link, reset( explode( ':', $args->path ) ) ) . '\'';
	}
	if( $Filesystem = $SqlDatabase->FetchObject( '
	SELECT * FROM `Filesystem` f
	WHERE 
		(
			f.GroupID IN (
						SELECT ug.UserGroupID FROM FUserToGroup ug, FUserGroup g
						WHERE 
							g.ID = ug.UserGroupID AND g.Type = \'Workgroup\' AND
							ug.UserID = \'' . $User->ID . '\'
					)
			OR
			f.UserID=\'' . $User->ID . '\' 
		)
		AND ' . $identifier . '
	' ) )
	{
		return $Filesystem;
	}
	return $identifier;
}

// Check for desktop events now
function checkDesktopEvents()
{
	// Returnvar
	$returnvar = [];
	
	// Check if we have files to import
	$files = [];
	if( file_exists( 'import' ) && $f = opendir( 'import' ) )
	{
		while( $file = readdir( $f ) )
		{
			if( $file{0} == '.' ) continue;
			$files[] = $file;
		}
		closedir( $f );
	}
	if( count( $files ) > 0 )
	{
		$returnvar['Import'] = $files;
	}
	// Count
	$c = 0;
	foreach( $returnvar as $k=>$v )
		$c++;
	if( $c > 0 )
		return json_encode( $returnvar );
	return false;
}



function curl_exec_follow( $cu, &$maxredirect = null )
{		
	$mr = 5;
	
	if ( ini_get( 'open_basedir' ) == '' && ini_get( 'safe_mode' == 'Off' ) )
	{
		curl_setopt( $cu, CURLOPT_FOLLOWLOCATION, $mr > 0 );
		curl_setopt( $cu, CURLOPT_MAXREDIRS, $mr );
	}
	else
	{
		curl_setopt( $cu, CURLOPT_FOLLOWLOCATION, false );
		
		if ( $mr > 0 )
		{
			$newurl = curl_getinfo( $cu, CURLINFO_EFFECTIVE_URL );
			$rch = curl_copy_handle( $cu );
			
			curl_setopt( $rch, CURLOPT_HEADER, true );
			curl_setopt( $rch, CURLOPT_NOBODY, true );
			curl_setopt( $rch, CURLOPT_FORBID_REUSE, false );
			curl_setopt( $rch, CURLOPT_RETURNTRANSFER, true );
			do
			{
				curl_setopt( $rch, CURLOPT_URL, $newurl );
				
				$header = curl_exec( $rch );
				
				if ( curl_errno( $rch ) ) 
				{
					$code = 0;
				}
				else
				{
					$code = curl_getinfo( $rch, CURLINFO_HTTP_CODE );
					
					if ( $code == 301 || $code == 302 || $code == 303 )
					{
						preg_match( '/Location:(.*?)\n/', $header, $matches );
						
						if ( !$matches )
						{
							preg_match( '/location:(.*?)\n/', $header, $matches );
						}
						
						$oldurl = $newurl;
						$newurl = trim( array_pop( $matches ) );
						
						if ( $newurl && !strstr( $newurl, 'http://' ) && !strstr( $newurl, 'https://' ) )
						{
							if ( strstr( $oldurl, 'https://' ) )
							{
								$parts = explode( '/', str_replace( 'https://', '', $oldurl ) );
								$newurl = ( 'https://' . reset( $parts ) . ( $newurl{0} != '/' ? '/' : '' ) . $newurl );
							}
							if ( strstr( $oldurl, 'http://' ) )
							{
								$parts = explode( '/', str_replace( 'http://', '', $oldurl ) );
								$newurl = ( 'http://' . reset( $parts ) . ( $newurl{0} != '/' ? '/' : '' ) . $newurl );
							}
							
						}
					}
					else
					{
						$code = 0;
					}
				}
			}
			while ( $code && --$mr );
			curl_close( $rch );
			if ( !$mr )
			{
				if ( $maxredirect === null )
				{
					return false;
				}
				else
				{
					$maxredirect = 0;
				}
				
				return false;
			}
			
			curl_setopt( $cu, CURLOPT_URL, $newurl );
		}
	}
	
	$cu = curl_exec( $cu );
	
	if( $cu )
	{
		return $cu;
	}
	
	return false;
}
	
	

if( isset( $args->command ) )
{
	switch( $args->command )
	{
		/*case 'copytest':
			include_once( 'php/classes/door.php' );
			$d = new Door( 'Home:' );
			$t = new Door( 'Documents:' );
			if( $f = $d->getFile( 'Home:FriendWorkspace.odt' ) )
			{
				$t->putFile( 'Documents:Telenor/FriendWorkspace.odt', $f );
				die( 'ok<!--separate-->' );
			}
			die( 'fail<!--separate-->{"response":"failed"}'  );
			break;*/
		case 'help':
			$commands = array(
				'ping', 'theme', 'systempath', 'software', 'proxycheck', 'proxyget', 
				'usersessionrenew', 'usersessions', 'userlevel', 'convertfile',
				'userlevel', 'convertfile', 'install', 'assign', 'doorsupport', 'setup',
				'languages', 'types', 'events', 'news', 'setdiskcover', 'getdiskcover', 'calendarmodules',
				'mountlist', 'mountlist_list', 'deletedoor', 'fileinfo', 
				'addfilesystem', 'editfilesystem', 'status', 'makedir', 'mount',
				'unmount', 'friendapplication', 'activateapplication', 'updateapppermissions',  
				'installapplication',  'uninstallapplication', 'package',  'updateappdata', 
				'setfilepublic', 'setfileprivate', 'zip', 'unzip', 'volumeinfo', 
				'securitydomains', 'systemmail', 'removebookmark', 'addbookmark', 
				'getbookmarks', 'listapplicationdocs', 'finddocumentation', 'userinfoget', 
				'userinfoset',  'useradd', 'checkuserbyname', 'userbetamail', 'listbetausers', 
				'usersetup', 'usersetupadd', 'usersetupapply', 'usersetupsave', 'usersetupdelete', 
				'usersetupget', 'workgroups', 'workgroupadd', 'workgroupupdate', 'workgroupdelete', 
				'workgroupget', 'setsetting', 'getsetting', 'listlibraries', 'listmodules', 
				'listuserapplications', 'getmimetypes',  'setmimetypes', 'deletemimetypes',
				'deletecalendarevent', 'getcalendarevents', 'addcalendarevent', 
				'listappcategories', 'systempath', 'listthemes', 'settheme', 'userdelete','userunblock', 
				'usersettings', 'listsystemsettings', 'savestate', 'getsystemsetting', 
				'saveserversetting', 'deleteserversetting', 'launch', 'friendversion'
			);
			sort( $commands );
			die( 'ok<!--separate-->{"Commands": ' . json_encode( $commands ) . '}' );
			break;
		case 'tinyurl':
			if( isset( $User ) )
				require( 'modules/system/include/tinyurl.php' );
			break;
		case 'ping':
			if( isset( $User ) )
			{
				$User->Loggedtime = mktime();
				$User->Save();
				die( 'ok' );
			}
			die( 'fail<!--separate-->{"response":"ping failed"}'  );
			break;
		case 'theme':
			require( 'modules/system/include/theme.php' );
			break;
		case 'systempath':
			require( 'modules/system/include/systempath.php' );
			break;
		case 'software':
			require( 'modules/system/include/software.php' );
			break;
		// Check a proxy connection
		case 'proxycheck':
			if( function_exists( 'curl_init' ) )
			{
				$c = curl_init();
				curl_setopt( $c, CURLOPT_URL, $args->args->url );
				curl_setopt( $c, CURLOPT_FAILONERROR, true );
				curl_setopt( $c, CURLOPT_NOBODY, true );
				curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
				if( $args->args->useragent )
				{
					curl_setopt( $c, CURLOPT_USERAGENT, $args->args->useragent );
				}
				if( $Config->SSLEnable )
				{
					curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, false );
				}
				
				if( function_exists( 'curl_exec_follow' ) )
				{
					$r = curl_exec_follow( $c );
				}
				else
				{
					$r = curl_exec( $c );
				}
				
				$k = curl_getinfo( $c );
				curl_close( $c );
				
				if( $r !== false && $k && ( $k['http_code'] == 200 || $k['http_code'] == 301 || $k['http_code'] == 206 ) )
				{
					die( 'ok<!--separate-->' . $k['http_code'] . '<!--separate-->' . print_r( $k,1 ) );
				}
				die( 'fail<!--separate-->' . ( $k ? ( $k['http_code'] . '<!--separate-->' . print_r( $k,1 ) ) : $r ) );
			}
			else
			{
				die( 'fail<!--separate-->function curl_init doesn\'t exist' );
			}
			die( 'totalfail<!--separate-->' . $r . '<!--separate-->' . $args->args->url );
			break;
		// Gives a proxy connection
		case 'proxyget':
			if( function_exists( 'curl_init' ) )
			{
				// Make sure we're getting an url!
				if( $args->args->url )
				{
					$str5 = substr( $args->args->url, 0, 5 );
					$str6 = substr( $args->args->url, 0, 6 );
					if( $str5 != 'http:' && $str6 != 'https:' )
					{
						die( 'fail<!--separate-->{"Response":"No valid url supplied!"}' );
					}
				}
				$c = curl_init( $args->args->url );
				$fields = [];
				foreach( $args->args as $k=>$v )
				{
					if( $k == 'url' ) continue;
					$fields[$k] = $v;
				}
				curl_setopt( $c, CURLOPT_POST, true );
				curl_setopt( $c, CURLOPT_POSTFIELDS, http_build_query( $fields ) );
				curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $c, CURLOPT_HTTPHEADER, array( 'Accept-charset: UTF-8' ) );
				curl_setopt( $c, CURLOPT_ENCODING, 'UTF-8' );
				if( $args->args->useragent )
				{
					curl_setopt( $c, CURLOPT_USERAGENT, $args->args->useragent );
				}
				if( $Config->SSLEnable )
				{
					curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, false );
				}
				
				if( function_exists( 'curl_exec_follow' ) )
				{
					$r = curl_exec_follow( $c );
				}
				else
				{
					$r = curl_exec( $c );
				}
				
				curl_close( $c );
				
				if( isset( $fields['rawdata'] ) && $fields['rawdata'] )
				{
					die( 'ok<!--separate-->' . $r );
				}
			}
			else
			{
				die( 'fail<!--separate-->function curl_init doesn\'t exist' );
			}
			if( !preg_match( '/\<html/i', $r ) )
			{
				// --- Check if result is xml or json -----------------------------
				if( strstr( substr( trim( $r ), 0, 200 ), "<?xml" ) )
				{
					if( function_exists( 'simplexml_load_string' ) )
					{
						class simple_xml_extended extends SimpleXMLElement
						{
							public function Attribute( $name )
							{
								foreach( $this->Attributes() as $key=>$val )
								{
									if( $key == $name )
									{
										return (string)$val;
									}
								}
							}
						}
						
						// TODO: Make support for rss with namespacing
						
						if( $xml = simplexml_load_string( trim( $r ), 'simple_xml_extended' ) )
						{
							die( 'ok<!--separate-->' . json_encode( $xml ) );
						}
						else
						{
							die( 'fail<!--separate-->couldn\'t convert string to object with function simplexml_load_string' );
						}
					}
					else
					{
						die( 'fail<!--separate-->function simplexml_load_string doesn\'t exist' );
					}
				}
				else if( json_decode( trim( $r ) ) && json_last_error() === 0 )
				{
					die( 'ok<!--separate-->' . $r );
				}
			}
			else
			{
				die( 'ok<!--separate-->' . $r );
			}
			die( 'totalfail<!--separate-->' . $r . '<!--separate-->' . $args->args->url );
			break;
		
		// Forcefully renew a session for a user
		case 'usersessionrenew':
			require( 'modules/system/include/usersessionrenew.php' );
			break;
		// Get a list of all active user sessions
		case 'usersessions':
			require( 'modules/system/include/usersessions.php' );
			break;
		case 'userlevel':
			if( $o = $SqlDatabase->FetchObject( '
				SELECT g.Name FROM FUserGroup g, FUserToGroup ug 
				WHERE 
					ug.UserID=\'' . $User->ID . '\' AND ug.UserGroupID = g.ID
					AND g.Type = \'Level\'
			' ) )
			{
				die( 'ok<!--separate-->' . $o->Name );
			}
			die( 'fail<!--separate-->{"response":"user level failed"}'  );
			break;
		case 'convertfile':
			require( 'modules/system/include/convertfile.php' );
			break;
		// Install / upgrade application from central Friend Store repo
		case 'install':
			require( 'modules/system/include/install.php' );
			break;
		case 'assign':
			$mode = $args->args->mode;
			if( !isset( $args->args->assign ) )
			{
				if( $devices = $SqlDatabase->fetchObjects( '
					SELECT f.Name, f.Mounted FROM 
						Filesystem f 
					WHERE 
						f.Type="Assign" AND f.UserID=\'' . $User->ID . '\'
					ORDER BY f.Name ASC
				' ) )
				{
					die( 'ok<!--separate-->' . json_encode( $devices ) );
				}
				die( 'fail<!--separate-->{"response":"assign failed"}'  );
			}
			$assign = $args->args->assign;
			$path = $args->args->path;
			
			
			// Remove others
			if( $mode ) $mode = strtolower( $mode );
			if( !$mode || ( $mode && $mode != 'add' ) )
			{
				$SqlDatabase->query( 'DELETE FROM `Filesystem` WHERE `Type` = "Assign" AND `UserID`=\'' . $User->ID . '\' AND `Name` = "' . mysqli_real_escape_string( $SqlDatabase->_link, str_replace( ':', '', trim( $assign ) ) ) . '"' );
			}
			
			// Just remove the assign
			if( $mode == 'remove' && !$path ) 
			{
				die( 'ok<!--separate-->{"response": "Filesystem was properly removed."}' );
			}
			
			// Add item
			$o = new dbIO( 'Filesystem' );
			$o->UserID = $User->ID;
			$o->Name = str_replace( ':', '', trim( $assign ) );
			$o->Type = 'Assign';
			$o->Load(); // Try to load..
			$o->Mounted = '0';
			$o->Config = '{"Invisible":"Yes"}';
			
			if( $mode == 'add' )
			{
				$o->Path = $o->Path ? ( ( $o->Path . ';' ) . $path ) : $path;
				
				// Process paths
				$all = explode( ';', $o->Path );
				
				// No duplicates
				$final = [];
				foreach( $all as $p )
				{
					$found = false;
					if( count( $final ) )
					{
						foreach( $final as $f )
						{
							if( $p == $f )
							{
								$found = true;
								break;
							}
						}
					}
					if( !$found ) $final[] = $p;
				}
				$o->Path = implode( ';', $final ); 
			}
			else if( $mode == 'remove' )
			{
				// Process paths
				$all = explode( ';', $o->Path );
				
				// Just remove one specified by $path
				$final = $o->Path;
				if( $mode == 'remove' )
				{
					$newAll = [];
					foreach( $all as $a )
					{
						if( $a != $path )
							$newAll[] = $a;
					}
					$final = $newAll;
				}
				$o->Path = implode( ';', $final );
			}
			else
			{
				$o->Path = $path;
			}
			$o->Save();
			if( $o->ID > 0 )
				die( 'ok' );
			die( 'fail<!--separate-->{"response":"assign failed"}'  );
			break;
		case 'doorsupport':
			if( $dir = opendir( 'devices/DOSDrivers' ) )
			{
				$str = '';
				while( $f = readdir( $dir ) )
				{
					if( $f{0} == '.' ) continue;
					if( file_exists( $g = 'devices/DOSDrivers/' . $f . '/door.js' ) )
						$str .= file_get_contents( $g ) . "\n";
				}
				closedir( $dir );
				if( strlen( $str ) )
					die( $str );
			}
			die( '' );
			break;
		// Just run some checks
		case 'setup':
			//include( 'modules/system/include/dbchecker.php' );
			die( 'ok' );
			break;
		case 'languages':
			include( 'modules/system/include/languages.php' );
			break;
		case 'getdosdrivericon':
			$f = isset( $args->dosdriver ) ? $args->dosdriver : die( '404' );
			if( strstr( $f, '..' ) ) die( '404' );
			if( file_exists( $fn = ( 'devices/DOSDrivers/' . $f . '/sysinfo.json' ) ) )
			{
				if( $o = json_decode( file_get_contents( $fn ) ) )
				{
					if( $o->group == 'Admin' && $level != 'Admin' ) die( '404' );
					if( !$o->icon ) die( '404' );
					
					if( file_exists( 'devices/DOSDrivers/' . $f . '/' . $o->icon ) )
					{
						FriendHeader( 'Content-Type: image/png' );
						die( file_get_contents( 'devices/DOSDrivers/' . $f . '/' . $o->icon ) );
					}
				}
			}
			die( '404' );
			break;
		case 'types':
			$out = [];
			if( $dir = opendir( 'devices/DOSDrivers' ) )
			{
				while( $f = readdir( $dir ) )
				{
					if( $f{0} == '.' ) continue;
					
					if( !file_exists( $fn = 'devices/DOSDrivers/' . $f . '/sysinfo.json' ) )
						continue;
					$o = file_get_contents( $fn );
					if( !( $o = json_decode( $o ) ) ) continue;
					
					// Admin filesystems can only be added by admin..
					if( $o->group == 'Admin' && $level != 'Admin' )
						continue;
					
					if( isset( $o->icon ) && file_exists( 'devices/DOSDrivers/' . $f . '/' . $o->icon ) )
					{
						$o->hasIcon = 'true';
					}
					else
					{
						$o->hasIcon = 'false';
					}
					
					$out[] = $o;
				}
				closedir( $dir );
			}
			if( count( $out ) > 0 )
			{
				die( 'ok<!--separate-->' . json_encode( $out ) );
			}
			die( 'fail<!--separate-->{"response":"types failed"}' );
			//die( 'ok<!--separate-->[{"type":"treeroot","literal":"Treeroot"},{"type":"local","literal":"Local filesystem"},{"type":"corvo","literal":"MySQL Based Filesystem"},{"type":"website","literal":"Mount websites as doors"}]' );
			break;
		// Get desktop events
		case 'events':
			if( $data = checkDesktopEvents() )
			{
				die( 'ok<!--separate-->' . $data );
			}
			die( 'fail<!--separate-->{"response":"events failed"}' );
			break;
		// Updates from Friend Software Labs!
		case 'news':
			if( ( $d = file_get_contents( 'resources/updates.html' ) ) )
			{
				die( 'ok<!--separate-->' . $d );
			}
			die( 'fail<!--separate-->{"response":"news failed"}'  );
			break;	
		case 'setdiskcover':
			require( 'modules/system/include/setdiskcover.php' );
			break;
		case 'getdiskcover':
			require( 'modules/system/include/getdiskcover.php' );
			break;
		// Available calendar modules
		case 'calendarmodules':
			$modules = [];
			if( $dir = opendir( 'modules' ) )
			{
				while( $file = readdir( $dir ) )
				{
					if( $file{0} == '.' ) continue;
					if( !is_dir( 'modules/' . $file ) ) continue;
					if( file_exists( 'modules/' . $file . '/calendarmodule.php' ) )
					{
						$o = new stdClass();
						$o->module = $file;
						$o->moduleName = ucfirst( $file );
						$modules[] = $o;
					}
				}
				closedir( $dir );
			}
			if( count( $modules ) )
			{
				die( 'ok<!--separate-->' . json_encode( $modules ) );
			}
			die( 'fail<!--separate-->' );
			break;
		// Get a list of mounted and unmounted devices
		case 'mountlist':
			if( $rows = $SqlDatabase->FetchObjects( '
				SELECT f.* FROM Filesystem f 
				WHERE 
					(
						f.UserID=\'' . mysqli_real_escape_string( $SqlDatabase->_link, $User->ID ) . '\' OR
						f.GroupID IN (
							SELECT ug.UserGroupID FROM FUserToGroup ug, FUserGroup g
							WHERE 
								g.ID = ug.UserGroupID AND g.Type = \'Workgroup\' AND
								ug.UserID = \'' . $User->ID . '\'
						)
					)
					' . ( isset( $args->args->type ) ? '
					AND f.Type = \'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->type ) . '\'
					' : '' ) . ( isset( $args->args->mounted ) ? '
					AND f.Mounted = \'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->mounted ) . '\'
					' : '' ) . '
				ORDER BY 
					f.Name ASC
			' ) )
			{
				// Let's censor some data..
				foreach( $rows as $k=>$v )
				{
					$rows[$k]->Username = '';
					$rows[$k]->Password = '';
				}
				die( 'ok<!--separate-->' . json_encode( $rows ) );
			}
			else
			{
				die( 'fail<!--separate-->no filesystems available<!--separate-->' . mysql_error() );
			}
			break;
			
		case 'mountlist_list':
			if( $level != 'Admin' ) die('fail<!--separate-->{"response":"mountlist_list failed"}' );
			
			if( !isset($args->args->userids) ) die('fail<!--seperate-->no userids given');
			$sql = '';
			if( isset($args->args->path) )
			{
				$type = ( isset($args->args->type) ? ' AND f.Type=\''. mysqli_real_escape_string( $SqlDatabase->_link, $args->args->type ) .'\'' : '' );
				$sql = '
					SELECT f.* FROM Filesystem f 
					WHERE 
						f.UserID IN (' . implode(',', $args->args->userids ) . ') ' . $type . ' AND f.Path LIKE \'%'. mysqli_real_escape_string( $SqlDatabase->_link, $args->args->path ) .'%\'
					ORDER BY 
						f.Name ASC
				';
			}
			
			if( $sql == '' ) die('fail<!--seperate-->no filter given');
			
			if( $rows = $SqlDatabase->FetchObjects( $sql ) )
			{
				// Let's censor some data..
				foreach( $rows as $k=>$v )
				{
					$rows[$k]->Username = '';
					$rows[$k]->Password = '';
				}
				die( 'ok<!--separate-->' . json_encode( $rows ) );
			}
			else
			{
				die( 'fail<!--separate-->server is defect. check database<!--separate-->' . mysql_error() );
			}
			
			break;
			
		case 'deletedoor':
			//admins can delete others users mounts...
			if( $level == 'Admin' && isset( $args->args->userid ) ) 
				$userid = intval( $args->args->userid );
			else
				$userid = $User->ID;
		
			if( $row = $SqlDatabase->FetchObject( '
				SELECT * FROM Filesystem 
				WHERE 
					UserID=\'' . $userid . '\' AND ID=\'' . intval( $args->args->id ) . '\' 
				LIMIT 1
			' ) )
			{
				if( !$User->SessionID )
				{
					die( 'fail<!--separate-->{"response":"deletedoor failed"}'  ); // print_r( $User, 1 )  ???
				}
				
				// Delete encryption keys if they exist
				if( $args->args->id > 0 )
				{
					$k = new DbIO( 'FKeys' );
					$k->RowType         = 'Filesystem';
					$k->RowID           = intval( $args->args->id, 10 );
					$k->UserID          = $userid;
					$k->IsDeleted 		= 0;
					if( $k->Load() ) $k->Delete();
				}
				
				include_once( 'php/classes/door.php' );
				
				//$Logger->log( 'Unmounting ' . $row->Name );
				if( $userid == $User->ID )
				{
					$door = new Door( $row->Name . ':' );
					$door->dosQuery( '/system.library/device/unmount?devname=' . $row->Name );					
				}
				
				$SqlDatabase->Query( 'DELETE FROM Filesystem WHERE UserID=\'' . $userid . '\' AND ID=\'' . intval( $args->args->id, 10 ) . '\'' );
				die( 'ok<!--separate-->' );
			}
			break;
		case 'fileinfo':
			require( 'modules/system/include/fileinfo.php' );
			break;
		// List available systems stored for the current user
		case 'filesystem':
			if( $row = $SqlDatabase->FetchObject( '
				SELECT f . * , u.Name AS Workgroup
				FROM Filesystem f
				LEFT JOIN FUserGroup u ON ( u.ID = f.GroupID AND u.Type =  "Workgroup" ) 
				WHERE
					f.UserID=\'' . $User->ID . '\' AND f.ID=\'' . intval( $args->args->id ) . '\' 
				LIMIT 1
			' ) )
			{
				if( $key = $SqlDatabase->FetchObject( '
					SELECT * 
					FROM 
						`FKeys` k 
					WHERE 
							k.UserID = \'' . $User->ID . '\'
						AND k.RowType = "Filesystem"
						AND k.RowID = \'' . intval( $args->args->id ) . '\'
						AND k.IsDeleted = "0" 
					ORDER 
						BY k.ID DESC
					LIMIT 1
				' ) )
				{
					$row->Key = $key;
				}
				
				die( 'ok<!--separate-->' . json_encode( $row ) );
			}
			break;
		case 'addfilesystem':
			$obj = $args->args;

			// some checks for correctness of request before we do stuff...
			if( !isset( $obj->Type ) ) die('fail<!--separate-->{"response":"add file system failed"}' );
			if( !file_exists( $fn = 'devices/DOSDrivers/' . $obj->Type . '/sysinfo.json' ) )	die('fail<!--seperate-->could not read config for chosen file system');
				
			$o = file_get_contents( $fn );
			if( !( $o = json_decode( $o ) ) ) die('fail<!--seperate-->could not read config for chosen file system');
				
			// Admin filesystems can only be added by admin..
			if( $o->group == 'Admin' && $level != 'Admin' )
				die('fail<!--separate-->unauthorised access attempt!');

			// we are allows to get here.
			if( isset( $obj->Name ) && strlen( $obj->Name ) > 0 )
			{
				// Support workgroups (that we are member of)!
				$groupID = '';
				if( isset( $args->args->Workgroup ) )
				{
					if( $group = $SqlDatabase->FetchObject( '
						SELECT ug.* FROM FUserGroup ug, FUserToGroup tg 
							WHERE ug.Name = "' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->Workgroup ) . '"
							AND ug.Type = "Workgroup"
							AND tg.UserGroupID = ug.ID
							AND tg.UserID = \'' . $User->ID . '\'
					' ) )
					{
						$groupID = $group->ID;
					}
				}
				
				// TODO: Add support for storing EncryptedKey with PublicKey connected to user
				
				// Set optional or extra args
				$config = new stdClass();
				foreach( $obj as $k=>$v )
				{
					if( substr( $k, 0, 5 ) == 'conf.' )
					{
						$key = end( explode( '.', $k ) );
						$config->$key = $v;
					}
				}
				
				$fs = new DbIO( 'Filesystem' );
				
				$fs->Name = $obj->Name;
				$fs->UserID = ( $level == 'Admin' && $obj->UserID ? $obj->UserID : $User->ID);
				$fs->GroupID = $groupID;
				if( !$fs->Load() )
				{
					$keys = array( 'Server', 'Name', 'Path', 'Type', 'ShortDescription', 'Username', 'Password', 'Mounted', 'PublicKey' );
					foreach( $keys as $kkey )
						if( !isset( $obj->$kkey ) )
							$obj->$kkey = '';
					
					// Commented out to use DbIO instead to get the ID after saving
					
					//$SqlDatabase->query( '
					//INSERT INTO Filesystem
					//( `Name`, `UserID`, `GroupID`, `Server`, `Port`, `Path`, `Type`, `ShortDescription`, `Username`, `Password`, `Mounted`, `Config` )
					//VALUES
					//(
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Name ) . '",
					//	"' . ( $level == 'Admin' && $obj->UserID ? $obj->UserID : $User->ID) . '", 
					//	"' . $groupID . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Server ) . '",
					//	"' . intval( $obj->Port, 10 ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Path ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Type ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->ShortDescription ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Username ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Password ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, isset( $obj->Mounted ) ? $obj->Mounted : '' ) . '",
					//	"' . mysqli_real_escape_string( $SqlDatabase->_link, json_encode( $config ) ) . '"
					//)
					//' );
					
					$f = new DbIO( 'Filesystem' );
					$f->Name             = mysqli_real_escape_string( $SqlDatabase->_link, $obj->Name );
					$f->UserID           = ( $level == 'Admin' && $obj->UserID ? $obj->UserID : $User->ID);
					$f->GroupID          = $groupID;
					$f->Server           = mysqli_real_escape_string( $SqlDatabase->_link, $obj->Server );
					$f->Port             = intval( $obj->Port, 10 );
					$f->Path             = mysqli_real_escape_string( $SqlDatabase->_link, $obj->Path );
					$f->Type             = mysqli_real_escape_string( $SqlDatabase->_link, $obj->Type );
					$f->ShortDescription = mysqli_real_escape_string( $SqlDatabase->_link, $obj->ShortDescription );
					$f->Username         = mysqli_real_escape_string( $SqlDatabase->_link, $obj->Username );
					$f->Password         = mysqli_real_escape_string( $SqlDatabase->_link, $obj->Password );
					$f->Mounted          = mysqli_real_escape_string( $SqlDatabase->_link, isset( $obj->Mounted ) ? $obj->Mounted : '' );
					$f->Config           = mysqli_real_escape_string( $SqlDatabase->_link, json_encode( $config ) );
					$f->Save();
					
					if( $f->ID > 0 && isset( $obj->EncryptedKey ) )
					{
						$k = new DbIO( 'FKeys' );
						$k->RowType         = 'Filesystem';
						$k->RowID           = $f->ID;
						$k->UserID          = ( $level == 'Admin' && $obj->UserID ? $obj->UserID : $User->ID);
						$k->IsDeleted 		= 0;
						if( !$k->Load() )
						{
							$k->DateCreated = date( 'Y-m-d H:i:s' );
						}
						$k->Type 			= $obj->Name;
						$k->Data            = $obj->EncryptedKey;
						$k->PublicKey       = $obj->PublicKey;
						$k->DateModified    = date( 'Y-m-d H:i:s' );
						$k->Save();
					}
					
					die( 'ok' );
				}
				die( 'fail<!--separate-->{"response":"add file system failed"}'  );
			}
			die( 'fail<!--separate-->{"response":"add file system failed"}'  );
		case 'editfilesystem':
			$obj = $args->args;
			
			// some checks for correctness of request before we do stuff...
			if( !isset( $obj->Type ) ) die('fail<!--separate-->{"response":"edit filesystem failed"}' );
			if( !file_exists( $fn = 'devices/DOSDrivers/' . $obj->Type . '/sysinfo.json' ) )	die('fail<!--seperate-->could not read config for chosen file system');
				
			$o = file_get_contents( $fn );
			if( !( $o = json_decode( $o ) ) ) die('fail<!--seperate-->could not read config for chosen file system');
				
			// Admin filesystems can only be added by admin..
			if( $o->group == 'Admin' && $level != 'Admin' )
				die('fail<!--separate-->unauthorised access attempt!');
			
			if( isset( $obj->ID ) && $obj->ID > 0 )
			{
				// Support workgroups (that we are member of)!
				$groupID = '';
				if( $group = $SqlDatabase->FetchObject( '
					SELECT ug.* FROM FUserGroup ug, FUserToGroup tg 
						WHERE ug.Name = "' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->Workgroup ) . '"
						AND ug.Type = "Workgroup"
						AND tg.UserGroupID = ug.ID
						AND tg.UserID = \'' . $User->ID . '\'
				' ) )
				{
					$groupID = $group->ID;
				}
				
				// TODO: Add support for updating EncryptedKey with PublicKey connected to user
				
				// Set optional or extra args
				$config = new stdClass();
				foreach( $obj as $k=>$v )
				{
					if( substr( $k, 0, 5 ) == 'conf.' )
					{
						$key = end( explode( '.', $k ) );
						$config->$key = $v;
					}
				}
				
				$SqlDatabase->query( '
				UPDATE Filesystem
					SET `Name` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Name ) . '", 
					`UserID` = "' . $User->ID . '", 
					`GroupID` = "' . $groupID . '", 
					`Server` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Server ) . '", 
					`Port` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Port ) . '", 
					`Path` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Path ) . '", 
					' . ( isset( $obj->Type ) ? ( '`Type` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Type ) . '",' ) : '' ) . '
					`ShortDescription` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->ShortDescription ) . '", 
					`Username` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Username ) . '", 
					'. ( isset($obj->Password) && $obj->Password != '' ? '`Password` = "' . mysqli_real_escape_string( $SqlDatabase->_link, $obj->Password ) . '",' : '' ) . ' 
					`Mounted` = "0",
					`Config` = "' . mysqli_real_escape_string( $SqlDatabase->_link, json_encode( $config ) ) . '"
				WHERE
					ID = \'' . intval( $obj->ID, 10 ) . '\'
				' );
				
				if( $obj->ID > 0 && isset( $obj->EncryptedKey ) )
				{
					$k = new DbIO( 'FKeys' );
					$k->RowType         = 'Filesystem';
					$k->RowID           = intval( $obj->ID, 10 );
					$k->UserID          = $User->ID;
					$k->IsDeleted 		= 0;
					if( !$k->Load() )
					{
						$k->DateCreated = date( 'Y-m-d H:i:s' );
					}
					$k->Type 			= $obj->Name;
					$k->Data            = $obj->EncryptedKey;
					$k->PublicKey       = $obj->PublicKey;
					$k->DateModified    = date( 'Y-m-d H:i:s' );
					$k->Save();
				}
				
				die( 'ok' );
			}
			die( 'fail<!--separate-->{"response":"edit fs failed"}'  );
			
		// Filesystem status
		case 'status':
			if( $args->devname )
			{
				$devname = str_replace( ':', '', $args->devname );
				if( $row = $SqlDatabase->FetchObject( '
					SELECT * FROM Filesystem f
					WHERE
						(
							f.UserID=\'' . $User->ID . '\' OR
							f.GroupID IN (
								SELECT ug.UserGroupID FROM FUserToGroup ug, FUserGroup g
								WHERE 
									g.ID = ug.UserGroupID AND g.Type = \'Workgroup\' AND
									ug.UserID = \'' . $User->ID . '\'
							)
						)
						AND f.Name=\'' . $devname . '\'
					LIMIT 1
				' ) )
				{
					die( $row->Mounted ? 'mounted' : 'unmounted' );
				}
				die( 'fail<!--separate-->{"response":"status failed"}'  );
			}
			break;
		case 'makedir':
			// Redirect it to the files module
			$args->command = 'dosaction';
			if( !isset( $args->args ) ) $args->args = new stdClass();
			$args->args->action = 'makedir';
			$args->args->path = $_REQUEST['path'];
			include( 'modules/files/module.php' );
			break;
		case 'mount':
			if( $args->devname )
			{
				$devname = trim( str_replace( ':', '', $args->devname ) );
				if( $row = $SqlDatabase->FetchObject( '
					SELECT * FROM Filesystem f
					WHERE
						f.Name=\'' . mysqli_real_escape_string( $SqlDatabase->_link, $devname ) . '\' AND
						( 
							f.UserID=\'' . intval( $User->ID ) .'\' OR
							f.GroupID IN (
								SELECT ug.UserGroupID FROM FUserToGroup ug, FUserGroup g
								WHERE 
									g.ID = ug.UserGroupID AND g.Type = \'Workgroup\' AND
									ug.UserID = \'' . intval( $User->ID ) . '\'
							)
						)
					LIMIT 1
				' ) )
				{
					//$Logger->log( 'Found fs ' . $row->Name . ':' );
					
					// TODO: Will be deprecated to be here'
					$path = isset( $args->path ) ? $args->path : ( isset( $args->args->path ) && $args->args->path ? $args->args->path : false );
					$test = 'devices/DOSDrivers/' . $row->Type . '/door.php';
					if( file_exists( $test ) )
					{
						$Logger->log( 'Found: ' . $test );
						$Logger->log( 'Found ' . 'devices/DOSDrivers/' . $row->Type . '/door.php' );
						
						$args->command = 'dosaction';
						$args->action = 'mount';
						
						$Logger->log( 'right before... ' );

						include_once( $test );
						
						$Logger->log('file was included');
						
						foreach( $row as $k=>$v )
							$door->$k = $v;



						if( $result = $door->dosAction( $args ) )
						{
							$Logger->log( 'Result: ' . $result );
							die( $result );
						}
						$Logger->log( 'Included..' );
					}
					else
					{
						$Logger->log('No door.php found for ' . $row->Name . ' at ' . $test);
					}
					die( 'ok<!--separate-->' );
				}
			}
			//$Logger->log( 'Failed..' );
			die( 'fail<!--separate-->{"response":"could not mount drive"}' );
			break;
		case 'unmount':
			if( $args->devname )
			{
				$devname = trim( str_replace( ':', '', $args->devname ) );
				if( $row = $SqlDatabase->FetchObject( '
					SELECT * FROM `Filesystem`
					WHERE
						(
							f.UserID=\'' . intval( $User->ID ) . '\' OR
							f.GroupID IN (
								SELECT ug.UserGroupID FROM FUserToGroup ug, FUserGroup g
								WHERE 
									g.ID = ug.UserGroupID AND g.Type = \'Workgroup\' AND
									ug.UserID = \'' . intval( $User->ID ) . '\'
							)
						)
						AND Name=\'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->devname ) . '\'
					LIMIT 1
				' ) )
				{
					// Only user can unmount
					$SqlDatabase->query( '
					UPDATE `Filesystem`
						SET `Mounted` = "0"
					WHERE
						`ID` = \'' . $row->ID . '\' AND UserID=\'' . $User->ID . '\'
					' );
					
					
					// TODO: Will be deprecated to be here'
					$Logger->log( 'Trying to use dosdriver to unmount drive ' . $devname . '..' );
					$test = 'devices/DOSDrivers/' . $row->Type . '/door.php';
					if( file_exists( $test ) )
					{
						include( $test );
						$args->command = 'dosaction';
						$args->action = 'unmount';
						if( $result = $door->dosAction( $args ) )
							die( $result );
					}
					
					die( 'ok<!--separate-->' );
				}
			}
			break;
		// Populate a sandbox
		case 'sandbox':
			require( 'modules/system/include/sandbox.php' );
			break;
		// Try to lauch friend application
		case 'friendapplication':
			require( 'modules/system/include/friendapplication.php' );
			break;
		// Activate an application for the user
		case 'activateapplication':
			require( 'modules/system/include/activateapplication.php' );
			break;
		case 'updateapppermissions':
			require( 'modules/system/include/updateapppermissions.php' );
			break;
		// Get a repository resource
		case 'resource':
			require( 'modules/system/include/resource.php' );
			break;
		// Install a friend package!
		case 'installpackage':
			require( 'modules/system/include/installpackage.php' );
			break;
		// Install an application available in the repo (sysadmin only!)
		case 'installapplication':
			require( 'modules/system/include/installapplication.php' );
			break;
		// Install an application available in the repo (sysadmin only!)
		case 'uninstallapplication':
			require( 'modules/system/include/uninstallapplication.php' );
			break;
		case 'package':
			require( 'modules/system/include/package.php' );
			break;
		case 'updateappdata':
			require( 'modules/system/include/updateappdata.php' );
			break;
		case 'setfilepublic':
			require( 'modules/system/include/setfilepublic.php' );
			break;
		case 'setfileprivate':
			require( 'modules/system/include/setfileprivate.php' );
			break;
		case 'zip':
			require( 'modules/system/include/zip.php' );
			break;
		case 'unzip':
			require( 'modules/system/include/unzip.php' );
			break;
		// Get information about a volume
		case 'volumeinfo':
			require( 'modules/system/include/volumeinfo.php' );
			break;
		// Remove a bookmark
		case 'securitydomains':
			require( 'modules/system/include/securitydomains.php' );
			break;
		case 'systemmail':
			require( 'modules/system/include/systemmail.php' );
			break;
		// Remove a bookmark
		case 'removebookmark':
			$s = new dbIO( 'FSetting' );
			$s->UserID = $User->ID;
			$s->Type = 'bookmark';
			$s->Key = $args->args->name;
			$s->Load(); // Try to load it
			if( isset( $s->ID ) && $s->ID > 0 )
			{
				$s->Delete();
				die( 'ok' );
			}
			die( 'fail<!--separate-->' . $s->_lastQuery );
			break;
		// Add a filesystem bookmark
		case 'addbookmark':
			$s = new dbIO( 'FSetting' );
			$s->UserID = $User->ID;
			$s->Type = 'bookmark';
			$s->Key = $args->args->path;
			$s->Load(); // Try to load it
			$s->Data = json_encode( $args->args );
			$s->Save(); // Save!
			if( isset( $s->ID ) && $s->ID > 0 )
			{
				die( 'ok' );
			}
			die( 'fail<!--separate-->' . $s->_lastQuery );
		// Get all bookmarks
		case 'getbookmarks':
			if( $rows = $SqlDatabase->FetchObjects( '
				SELECT * FROM `FSetting` WHERE
				`UserID`=\'' . $User->ID . '\' AND
				`Type`=\'bookmark\'
				ORDER BY `Key` ASC
			' ) )
			{
				$result = array();
				foreach( $rows as $row )
				{
					$data = json_decode( $row->Data );
					$o = new stdClass();
					$o->name = $data->name;
					$o->path = $data->path;
					$result[] = $o;
				}
				die( 'ok<!--separate-->' . json_encode( $result ) );
			}
			die( 'fail<!--separate-->{"response":"getbookmarks failed"}'  );
		case 'getlocale':
			require( 'modules/system/include/getlocale.php' );
			break;
		// Gets a list of available filesystem "drivers"
		case 'listfilesystems':
			
			break;
		// Get the whole or components of the dos driver gui
		case 'dosdrivergui':
			if( !isset( $args->args->type ) ) die( 'fail<!--separate-->{"response":"dos driver gui failed"}'  );
			if( isset( $args->args->component ) && isset( $args->args->language ) )
			{
				if( $args->args->component == 'locale' )
				{
					$f = 'devices/DOSDrivers/' . $args->args->type . '/Locale/' . $args->args->language . '.lang';
					if( file_exists( $f ) )
					{
						die( 'ok<!--separate-->' . file_get_contents( $f ) );
					}
					die( 'fail<!--separate-->' . $f );
				}
			}
			if( file_exists( $f = ( 'devices/DOSDrivers/' . $args->args->type . '/gui.html' ) ) )
			{
				die( 'ok<!--separate-->' . file_get_contents( $f ) );
			}
			die( 'fail<!--separate-->{"response":"dosdrivergui failed"}'  );
			break;
		case 'evaluatepackage':
			require( 'modules/system/include/evaluatepackage.php' );
			break;
		case 'repositorysoftware':
			require( 'modules/system/include/repositorysoftware.php' );
			break;
		case 'listapplicationdocs':
			if( $installed = $SqlDatabase->FetchObjects( '
				SELECT a.* FROM FApplication a, FUserApplication ua
				WHERE
					a.ID = ua.ApplicationID
				AND
					ua.UserID = \'' . $User->ID . '\'
				ORDER BY a.Name DESC
			' ) )
			{
				$out = [];
				foreach( $installed as $inst )
				{
					$ppath = 'resources/webclient/apps/';
					$dpath = 'Documentation/';
					$docnm = 'index.html';
					$final = $ppath . $inst->Name . '/' . $dpath . $docnm;
					if( file_exists( $ppath . $inst->Name ) && file_exists( $ppath . $inst->Name . '/' . $dpath ) && file_exists( $final ) )
					{
						$o = new stdClass();
						$o->Path = 'System:Documentation/Applications/' . $inst->Name;
						$o->Title = $inst->Name;
						$o->Filename = $inst->Name;
						$o->Type = 'DormantFunction';
						$o->Filesize = 16;
						$o->Module = 'Files';
						$o->Command = 'dormant';
						$o->Position = 'left';
						$o->IconFile = 'gfx/icons/128x128/mimetypes/text-enriched.png';
						$out[] = $o;
					}
				}
				die( 'ok<!--separate-->' . json_encode( $out ) );
			}
			die( 'fail<!--separate-->' );
			break;
		case 'finddocumentation':
			if( isset( $args->args->path ) && substr( strtolower( $args->args->path ), 0, 7 ) == 'system:' )
			{
				$p = strtolower( preg_replace( '/[^a-z]+/i', '_', end( explode( ':', $args->args->path ) ) ) ) . '.html';
				if( file_exists( 'resources/repository/onlinedocs/' . $p ) )
				{
					die( 'ok<!--separate-->' . file_get_contents( 'resources/repository/onlinedocs/' . $p ) );
				}
				die( 'fail<!--separate-->{"response":"find docs failed"}'  );
			}
			else if( $installed = $SqlDatabase->FetchObjects( '
				SELECT a.* FROM FApplication a, FUserApplication ua
				WHERE
					a.ID = ua.ApplicationID
				AND
					ua.UserID = \'' . $User->ID . '\'
				ORDER BY a.Name DESC
			' ) )
			{
				$out = [];
				foreach( $installed as $inst )
				{
					$ppath = 'resources/webclient/apps/';
					$dpath = 'Documentation/';
					$docnm = 'index.html';
					$final = $ppath . $inst->Name . '/' . $dpath . $docnm;
					if( $inst->Name == $args->args->doc && file_exists( $ppath . $inst->Name ) && file_exists( $ppath . $inst->Name . '/' . $dpath ) && file_exists( $final ) )
					{
						die( 'ok<!--separate-->' . file_get_contents( $final ) );
					}
				}
			}
			die( 'fail<!--separate-->' );
			break;
		// Get a list of users
		// TODO: Permissions!!! Only list out when you have users below your
		//                      level, unless you are Admin
		case 'listusers':
			if( $level != 'Admin' ) die('fail<!--separate-->{"response":"list users failed Error 1"}' );
			
			if( $users = $SqlDatabase->FetchObjects( '
				SELECT u.*, g.Name AS `Level` FROM 
					`FUser` u, `FUserGroup` g, `FUserToGroup` ug
				WHERE
					    u.ID = ug.UserID
					AND g.ID = ug.UserGroupID
					AND g.Type = "Level"
				GROUP BY u.ID, g.Name
				ORDER BY 
					u.FullName ASC
			' ) )
			{
				$out = [];
				foreach( $users as $u )
				{
					$keys = [ 'ID', 'Name', 'Password', 'FullName', 'Email', 'CreatedTime', 'Level' ];
					$o = new stdClass();
					foreach( $keys as $key )
					{
						$o->$key = $u->$key;
					}
					$out[] = $o;
				}
				die( 'ok<!--separate-->' . json_encode( $out ) );
			}
			die( 'fail<!--separate-->{"response":"list users failed Error 2"}'  );
			break;
		
		// Get detailed info about a user
		// TODO: Permissions!!! Only access users if you are admin!
		case 'userinfoget':
			if( isset($args->args->id) )
				$uid = $args->args->id;
			else
				$uid = $User->ID;
			if( $level == 'Admin' || $uid == $User->ID )
			{
				// Create FKeys table for storing encrypted keys connected to user
				$t = new dbTable( 'FKeys' );
				if( !$t->load() )
				{
					$SqlDatabase->Query( '
					CREATE TABLE IF NOT EXISTS `FKeys` (
					 `ID` bigint(20) NOT NULL AUTO_INCREMENT,
					 `UserID` bigint(20) NOT NULL,
					 `UniqueID` varchar(255) NOT NULL,
					 `RowID` bigint(20) NOT NULL,
					 `RowType` varchar(255) NOT NULL,
					 `Type` varchar(255) NOT NULL,
					 `Blob` longblob,
					 `Data` text,
					 `PublicKey` text,
					 `DateModified` datetime NOT NULL,
					 `DateCreated` datetime NOT NULL,
					 `IsDeleted` tinyint(4) NOT NULL,
					 PRIMARY KEY (`ID`)
					) 
					' );
				}
				
				if( $userinfo = $SqlDatabase->FetchObject( '
					SELECT 
						u.*, 
						g.Name AS `Level`, 
						wg.Name AS `Workgroup` 
					FROM 
						`FUser` u, 
						`FUserGroup` g, 
						`FUserToGroup` ug 
							LEFT JOIN `FUserGroup` wg ON 
							(
									ug.UserID = \'' . $uid . '\' 
								AND wg.ID = ug.UserGroupID 
								AND wg.Type = "Workgroup" 
							)
					WHERE 
							u.ID = ug.UserID 
						AND g.ID = ug.UserGroupID 
						AND u.ID = \'' . $uid . '\' 
						AND g.Type = \'Level\'
				' ) )
				{
					$gds = '';
					
					// TODO: Fix this sql code to work with workgroup, code under is temporary
					if( !$userinfo->Workgroup && ( $wgs = $SqlDatabase->FetchObjects( '
						SELECT
							g.ID,
							g.Name AS `Workgroup` 
						FROM 
							`FUserGroup` g, 
							`FUserToGroup` ug 
						WHERE 
								ug.UserID = \'' . $uid . '\' 
							AND g.ID = ug.UserGroupID 
							AND g.Type = "Workgroup" 
					' ) ) )
					{
						$ugs = array();
						
						foreach( $wgs as $wg )
						{
							$gds = ( $gds ? ( $gds . ',' . $wg->ID ) : $wg->ID );
							$ugs[] = $wg->Workgroup;
						}
						
						if( $ugs )
						{
							$userinfo->Workgroup = implode( ', ', $ugs );
						}
					}
					
					$gds = false;
					
					if( $sts = $SqlDatabase->FetchObjects( '
						SELECT g.ID, g.Name, ug.UserID' . ( $gds ? ', wg.UserID AS SetupGroup' : '' ) . ' 
						FROM 
							`FUserGroup` g 
								' . ( $gds ? '
								LEFT JOIN `FUserGroup` wg ON 
								(
										wg.Name = g.ID 
									AND wg.Type = \'SetupGroup\' 
									AND wg.UserID IN (' . $gds . ') 
								)
								' : '' ) . '
								LEFT JOIN `FUserToGroup` ug ON 
								(
										g.ID = ug.UserGroupID 
									AND g.Type = \'Setup\' 
									AND ug.UserID = \'' . $uid . '\' 
								)
						WHERE g.Type = \'Setup\' 
						ORDER BY g.Name ASC 
					' ) )
					{
						$userinfo->Setup = $sts;
					}
					
					if( $keys = $SqlDatabase->FetchObjects( '
						SELECT * 
						FROM 
							`FKeys` k 
						WHERE 
							k.UserID = \'' . $uid . '\' 
						ORDER 
							BY k.ID ASC 
					' ) )
					{
						$userinfo->Keys = $keys;
					}
					
					die( 'ok<!--separate-->' . json_encode( $userinfo ) );
				}
			}
			die( 'fail<!--separate-->{"response":"user info get failed"}'  );
			break;
		// Set info on a user
		// TODO: Update with correct encryption algo
		case 'userinfoset':	
			if( $level == 'Admin' || $args->args->id == $User->ID )
			{
				$u = new dbIO( 'FUser' );
				if( $u->Load( $args->args->id ) )
				{
					$u->FullName = $args->args->FullName;
					$u->Name     = $args->args->Name;
					$u->Email    = $args->args->Email;
					if( isset( $args->args->Password ) && $args->args->Password != '********' )
						$u->Password = $args->args->Password;
					$u->Save();
					if( isset( $args->args->Level ) )
					{
						// Check if the user group exists
						$g = false;
						if( !$g = $SqlDatabase->FetchObject( '
							SELECT * FROM `FUserGroup` g 
							WHERE 
								g.Name = \'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->Level ) . '\'' 
						) )
						{
							$g = new dbIO( 'FUserGroup' );
							$g->Name = $args->args->Level;
							$g->Type = 'Level';
							$g->Load();
							$g->Save();
						}
						// If we have a group
						if( isset( $g->ID ) && $g->ID > 0 )
						{
							if( $ugs = $SqlDatabase->FetchObjects( '
								SELECT 
									g.* 
								FROM 
									`FUserGroup` g, 
									`FUserToGroup` ug, 
								WHERE 
										g.Type = "Level" 
									AND ug.UserGroupID = g.ID 
									AND ug.UserID = \'' . $u->ID . '\' 
							' ) )
							{
								foreach( $ugs as $ug )
								{
									$SqlDatabase->query( '
										UPDATE FUserToGroup 
											SET UserGroupID = \'' . $g->ID . '\' 
										WHERE 
												UserGroupID = \'' . $ug->ID . '\'
											AND UserID = \'' . $u->ID . '\' 
									' );
								}
							}
							else
							{
								$ug = new dbIO( 'FUserToGroup' );
								$ug->UserGroupID = $g->ID;
								$ug->UserID = $u->ID;
								$ug->Load();
								$ug->Save();
							}
							
							die( 'ok' );
						}
					}
					die( 'ok' );
				}
			}
			die( 'fail<!--separate-->{"response":"user info set failed"}'  );
		// Add a new user
		// TODO: Permissions! ONly admin can do this!
		case 'useradd':
			if( $level == 'Admin' )
			{
				// Make sure we have the "User" type group
				$g = new dbIO( 'FUserGroup' );
				$g->Name = 'User';
				$g->Load();
				$g->Save();
				
				if( $g->ID > 0 )
				{
					// Create the new user
					$u = new dbIO( 'FUser' );
					$u->Password = md5( rand(0,999) + microtime() );
					$u->Name = 'Unnamed user';
					$u->FullName = 'Unnamed user';
					$u->Save();
					
					if( $u->ID > 0 )
					{
						$SqlDatabase->query( 'INSERT INTO FUserToGroup ( UserID, UserGroupID ) VALUES ( \'' . $u->ID . '\', \'' . $g->ID . '\' )' );
						die( 'ok<!--separate-->' . $u->ID );
					}
				}
			}
			die( 'fail<!--separate-->{"response":"user add failed"}'  );
		// 
		case 'checkuserbyname':
			if( $level == 'Admin' || $args->args->id == $User->ID )
			{
				if( $userinfo = $SqlDatabase->FetchObject( '
					SELECT `ID` FROM `FUser` WHERE Name = \'' . $args->args->username . '\'
				' ) )
				{
					die( 'ok<!--separate-->userexists' );
				}
				else
				{
					die( 'ok<!--separate-->userdoesnotexist' );
				}
			}
			die( 'fail<!--separate-->{"response":"checkuserbyname failed"}'  );
			break;	
		case 'userbetamail':		
		case 'listbetausers':
			require( 'modules/system/include/betaimport.php' );
			break;
		// List setup
		case 'usersetup':
			require( 'modules/system/include/usersetup.php' );
			break;
		// Add setup
		case 'usersetupadd':
			require( 'modules/system/include/usersetupadd.php' );
			break;
		// Apply setup
		case 'usersetupapply':
			require( 'modules/system/include/usersetupapply.php' );
			break;
		// Save setup
		case 'usersetupsave':
			require( 'modules/system/include/usersetupsave.php' );
			break;
		// Delete setup
		case 'usersetupdelete':
			require( 'modules/system/include/usersetupdelete.php' );
			break;
		// Get setup
		case 'usersetupget':
			require( 'modules/system/include/usersetupget.php' );
			break;
		// List workgroups
		case 'workgroups':
			require( 'modules/system/include/workgroups.php' );
			break;
		// Add a workgroup
		case 'workgroupadd':
			require( 'modules/system/include/workgroupadd.php' );
			break;
		// Update a workgroup
		case 'workgroupupdate':
			require( 'modules/system/include/workgroupupdate.php' );
			break;
		// Delete a workgroup
		case 'workgroupdelete':
			require( 'modules/system/include/workgroupdelete.php' );
			break;
		// Get a workgroup
		case 'workgroupget':
			require( 'modules/system/include/workgroupget.php' );
			break;
			
		case 'setsetting':
			require( 'modules/system/include/setsetting.php' );
			break;
		case 'getsetting':
			require( 'modules/system/include/getsetting.php' );
			break;
		case 'listlibraries':
			require( 'modules/system/include/listlibraries.php' );
			break;
		case 'listmodules':
			require( 'modules/system/include/modules.php' );
			break;
		case 'listuserapplications':
			require( 'modules/system/include/listuserapplications.php' );
			break;
		case 'getmimetypes':
			require( 'modules/system/include/getmimetypes.php' );
			break;
		case 'setmimetypes':
			require( 'modules/system/include/setmimetypes.php' );
			break;
		case 'deletemimetypes':
			require( 'modules/system/include/deletemimetypes.php' );
			break;
		case 'deletecalendarevent':
			require( 'modules/system/include/deletecalendarevent.php' );
			break;
		case 'getcalendarevents':
			require( 'modules/system/include/getcalendarevents.php' );
			break;
		case 'addcalendarevent':
			require( 'modules/system/include/addcalendarevent.php' );
			break;
		// List the categories of apps!
		case 'listappcategories':
			require( 'modules/system/include/appcategories.php' );
			break;
		// Handle system paths
		case 'systempath':
			require( 'modules/system/include/systempath.php' );
			break;
		// Get a list of available themes
		case 'listthemes':
			require( 'modules/system/include/themes.php' );
			break;
		case 'settheme':
			$o = new dbIO( 'FSetting' );
			$o->UserID = $User->ID;
			$o->Type = 'system';
			$o->Key = 'theme';
			$o->Load();
			$o->Data = $args->args->theme;
			$o->Save();
			if( $o->ID > 0 )
				die( 'ok' );
			die( 'fail<!--separate-->{"response":"set theme failed"}'  );
		case 'userdelete':
			if( $level != 'Admin' ) die('fail<!--separate-->{"response":"user delete failed"}' );
			$u = new dbIO( 'FUser' );
			if( $u->Load( $args->args->id ) )
			{
				$SqlDatabase->query( 'DELETE FROM `FSetting` WHERE UserID=\'' . $u->ID . '\'' );
				$SqlDatabase->query( 'DELETE FROM `DockItem` WHERE UserID=\'' . $u->ID . '\'' );
				$u->Delete();
				die( 'ok' );
			}
			die( 'fail<!--separate-->{"response":"user delete failed"}'  );
		case 'userunblock':
			if( $level != 'Admin' ) die('fail<!--separate-->{"response":"userunblock failed"}' );
			$u = new dbIO( 'FUser' );
			if( $u->Load( $args->args->id ) )
			{
				$unblockquery = 'INSERT INTO FUserLogin (`UserID`,`Login`,`Information`,`LoginTime`) VALUES ('. $u->ID .',\''. $u->Name .'\',\'Admin unblock\',\''. time() .'\')';
				$SqlDatabase->query( $unblockquery );
				die( 'ok' );
			}
			die( 'fail<!--separate-->{"response":"userunblock failed"}'  );
		case 'usersettings':
			// Settings object
			$s = new stdClass();
			
			// The first login test!
			include( 'modules/system/include/firstlogin.php' );
			
			// Theme information
			$o = new dbIO( 'FSetting' );
			$o->UserID = $User->ID;
			$o->Type = 'system';
			$o->Key = 'theme';
			$o->Load();

			$s->Theme = $o->ID > 0 ? $o->Data : 'friendup'; // default theme set to friendup
			
			// Get all mimetypes!
			$types = [];
			if( $rows = $SqlDatabase->FetchObjects( '
				SELECT * FROM FSetting s
				WHERE
					s.UserID = \'' . $User->ID . '\'
					AND
					s.Type = \'mimetypes\'
				ORDER BY s.Data ASC
			' ) )
			{
				foreach( $rows as $row )
				{
					$found = false;
					if( count( $types ) )
					{
						foreach( $types as $type )
						{
							if( $type->executable == $row->Data )
							{
								$type->types[] = $row->Key;
								$found = true;
							}
						}
					}
					if( !$found )
					{
						$o = new stdClass();
						$o->executable = $row->Data;
						$o->types = array( $row->Key );
						$types[] = $o;
					}
				}
			}
			$s->Mimetypes = $types;
			
			die( 'ok<!--separate-->' . json_encode( $s ) );
			
			
		case 'listsystemsettings':
			if( $rows = $SqlDatabase->FetchObjects( '
				SELECT * FROM FSetting s
				WHERE
					s.UserID = \'-1\'
				ORDER BY s.Key ASC
			' ) )
			{
				die( 'ok<!--separate-->' . json_encode( $rows ) );
			}
			else
			{
				die('ok<!--separate-->nosettingsfound');	
			}
			
			break;
		
		// Save the application state
		case 'savestate':
			require( 'modules/system/include/savestate.php' );
			break;
		
		case 'getsystemsetting':
			if( $args->args->type && $args->args->key && $rows = $SqlDatabase->FetchObjects( '
				SELECT * FROM FSetting s
				WHERE
					s.UserID = \'-1\'
				AND s.Type = \''. $args->args->type .'\'
				AND s.Key = \''. $args->args->key .'\'
				ORDER BY s.Key ASC
			' ) )
			{
				die( 'ok<!--separate-->' . json_encode( $rows ) );
			}
			else
			{
				die('ok<!--separate-->settingnotfouns');	
			}
			break;
			
		case 'saveserversetting':
			if( $level == 'Admin' && $args->args->settingsid && $args->args->settings )
			{
				$SqlDatabase->query( 'UPDATE `FSetting` SET Data=\''. mysqli_real_escape_string( $SqlDatabase->_link, $args->args->settings ) .'\' WHERE ID=\'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->settingsid ) . '\'' );
				die('ok<!--separate-->' .$args->args->settingsid );
			}
			else if( $level == 'Admin' && $args->args->key && $args->args->type )
			{
				$SqlDatabase->query( 'UPDATE `FSetting` SET Data=\''. mysqli_real_escape_string( $SqlDatabase->_link, $args->args->settings ) .'\' WHERE ID=\'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->settingsid ) . '\'' );
				$o = new dbIO( 'FSetting' );
				$o->UserID = '-1';
				$o->Type = $args->args->type;
				$o->Key = $args->args->key;
				$o->Save();
				if( $o->ID > 0 )
				{
					die('ok<!--separate-->' . $o->ID );
				}
			}
			else
			{
				die('fail<!--separate-->You are ' . $level );
			}
			die( 'fail<!--separate-->{"response":"saveserversettings failed"}'  );
		
		case 'deleteserversetting':
			if( $level == 'Admin' && $args->args->sid )
			{
				$SqlDatabase->query( 'DELETE FROM `FSetting` WHERE ID=\'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->sid ) . '\'' );
				die('ok<!--separate-->' .$args->args->settingsid );
			}
			break;
			
		// Launch an app...
		case 'launch':
			require( 'modules/system/include/launch.php' );
			break;
			
			
		// handle FriendUp version
		case 'friendversion':
			require( 'modules/system/include/friendversion.php' );
			break;
		
		// NATIVE version commands ---------------------------------------------
		
		// These functions are insecure. Commented out, we do not need them
		// Get all windows managed by friend core
		/*case 'list_windows':
			require( 'modules/system/include/znative_list_windows.php' );
			break;
		// Resize a window mbfc
		case 'window_resize':
			require( 'modules/system/include/znative_window_resize.php' );
			break;
		// Minimize a window mbfc
		case 'window_minimize':
			require( 'modules/system/include/znative_window_minimize.php' );
			break;
		// Maximize a window mbfc
		case 'window_maximize':
			require( 'modules/system/include/znative_window_maximize.php' );
			break;
		// Maximize a window mbfc
		case 'window_restore':
			require( 'modules/system/include/znative_window_restore.php' );
			break;
		// List apps running mbfc
		case 'list_apps':
			require( 'modules/system/include/znative_list_apps.php' );
			break;
		// Kill an app mbfc
		case 'kill_app':
			require( 'modules/system/include/znative_kill_app.php' );
			break;
		case 'launch_app':
			require( 'modules/system/include/znative_launch_app.php' );
			break;*/
	}
}

// End of the line
die( 'fail<!--separate-->{"response":"uncaught command exception"}' ); //end of the line<!--separate-->' . print_r( $args, 1 ) . '<!--separate-->' . ( isset( $User ) ? $User : 'No user object!' ) );


?>
