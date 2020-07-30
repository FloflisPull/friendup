/*©mit**************************************************************************
*                                                                              *
* This file is part of FRIEND UNIFYING PLATFORM.                               *
* Copyright (c) Friend Software Labs AS. All rights reserved.                  *
*                                                                              *
* Licensed under the Source EULA. Please refer to the copy of the MIT License, *
* found in the file license_mit.txt.                                           *
*                                                                              *
*****************************************************************************©*/
/** @file
 * 
 *  Role Manager
 *
 * file contain definitions related to RoleManager
 *
 *  @author PS (Pawel Stefanski)
 *  @date created 11/2019
 */

#include "mitra_manager.h"
#include <system/systembase.h>

/*

 */

/**
 * Create RoleManager
 *
 * @param sb pointer to SystemBase
 * @return RoleManager structure
 */
MitraManager *MitraManagerNew( void *sb )
{
	MitraManager *mm;
	
	//SELECT fu.ID,fu.Name FROM FUser fu,FUserToGroup futg WHERE fu.ID = futg.UserID AND futg.UserGroupID IN (SELECT ID FROM FUserGroup WHERE `Name`="User" AND `Type` = "SAML" ) AND futg.UserID = "'. mysqli_real_escape_string( $SqlDatabase->_link, $User->ID ) .'"
	
	//SELECT Data FROM FSetting s WHERE s.UserID = '-1' AND s.Type = 'mitra' AND s.Key = 'database';
	
	// getMitraUser
	// SELECT gu.user_id FROM guacamole_user gu WHERE gu.username = \'mitra_'. mysqli_real_escape_string( $mitradb->_link, $friendusername ) .'\' OR gu.username = \'mitra_frienduser_'. intval( $frienduserid ) .'\''
	//
	//$checkquery = 'SELECT * FROM guacamole_connection_parameter gcp WHERE gcp.connection_id IN ( ' .
	//                                                                    'SELECT connection_id FROM guacamole_connection_permission WHERE entity_id IN ( ' .
	//                                                                    '       SELECT entity_id FROM guacamole_user WHERE user_id = \''. getMitraUser( $User->ID,$User->Name,false ) .'\' '.
	//                                                                    ') AND connection_id = \'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->data->connection ) . '\')';

	
	//                                                                    'SELECT connection_id FROM guacamole_connection_permission WHERE entity_id IN ( ' .
	//                                                                    '       SELECT entity_id FROM guacamole_user WHERE user_id='SELECT gu.user_id FROM guacamole_user gu WHERE gu.username = 'mitra_%s' OR gu.username='mitra_frienduser_%s'' '.
	//                                                                    ') AND connection_id = \'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->data->connection ) . '\')'
	 
	
	if( ( mm = FCalloc( 1, sizeof( MitraManager ) ) ) != NULL )
	{
		mm->mm_SB = sb;
		SystemBase *l = (SystemBase *)sb;
		
		SQLLibrary *sqllib = l->LibrarySQLGet( l );
	
		DEBUG("[MitraManagerNew] mount\n");
	
		if( sqllib != NULL )
		{
			void *res = sqllib->Query( sqllib, "SELECT Data FROM FSetting s WHERE s.UserID='-1' AND s.Type='mitra' AND s.Key='database'" );
			char *host = NULL;
			char *dbname = NULL;
			char *login = NULL;
			char *pass = NULL;
			int port = 3306;

			// get fields from query
			if( res != NULL )
			{
				char **row;
				
				DEBUG("[MitraManagerNew] guacamole settings found!\n");
			
				while( ( row = sqllib->FetchRow( sqllib, res ) ) ) 
				{
					if( row[ 0 ] != NULL )
					{
						char *in = (char *)row[ 0 ];
						jsmn_parser p;
						jsmntok_t t[128]; // We expect no more than 128 tokens
					
						jsmn_init( &p );
						int r = jsmn_parse( &p, row[ 0 ] , strlen( row[ 0 ] ), t, 256 );

						// Assume the top-level element is an object 
						if( r > 1 && t[0].type == JSMN_OBJECT )
						{
							//{"host":"localhost","username":"guac","password":"IkkeHeltStabile2000","database":"guacamole"} 
						
							host = StringDuplicateN( in + t[ 2 ].start, (int)(t[ 2 ].end-t[ 2 ].start) );
							login = StringDuplicateN( in + t[ 4 ].start, (int)(t[ 4 ].end-t[ 4 ].start) );
							pass = StringDuplicateN( in + t[ 6 ].start, (int)(t[ 6 ].end-t[ 6 ].start) );
							dbname = StringDuplicateN( in + t[ 8 ].start, (int)(t[ 8 ].end-t[ 8 ].start) );
						}
					}
				}	// while
				DEBUG("[MitraManagerNew]After while. Login: %s host: %s pass: %s dbname: %s\n", login, host, pass, dbname );
				sqllib->FreeResult( sqllib, res );
			} // res != NULL
		
			int error;
		
			mm->mm_Sqllib = (struct SQLLibrary *)LibraryOpen( l, l->sl_DefaultDBLib, 0 );
			if( mm->mm_Sqllib != NULL )
			{
				DEBUG("[MitraManagerNew] setting up mysql connection\n");
				error = mm->mm_Sqllib->Connect( mm->mm_Sqllib, host, dbname, login, pass, port );
				if( error != 0 )
				{
					FERROR("[MitraManagerNew] There is a problem with guacamole DB connection\n");
				}
			}
		
			if( host != NULL )
			{
				FFree( host );
			}
			if( login != NULL )
			{
				FFree( login );
			}
			if( pass != NULL )
			{
				FFree( pass );
			}
			if( dbname != NULL )
			{
				FFree( dbname );
			}
		}	// res != NULL
	}
	return mm;
}

/**
 * Delete MitraManager
 *
 * @param mmgr pointer to MitraManager structure which will be deleted
 */
void MitraManagerDelete( MitraManager *mmgr )
{
	DEBUG("[MitraManagerDelete] start\n");
	if( mmgr != NULL )
	{
		if( mmgr->mm_Sqllib != NULL )
		{
			LibraryClose( mmgr->mm_Sqllib );
		}
		FFree( mmgr );
	}
	DEBUG("[MitraManagerDelete] end\n");
}

/**
 * Get user data
 *
 * @param mmgr pointer to MitraManager structure which will be deleted
 */
char *MitraManagerGetUserData( MitraManager *mmgr, char *username )
{
	char *retValue = NULL;
	
	DEBUG("[MitraManagerGetUserData] start\n");
	
	if( mmgr->mm_Sqllib != NULL )
	{
		char sqlTmp[ 512 ];
		snprintf( sqlTmp, sizeof(sqlTmp), "SELECT * FROM guacamole_connection_parameter gcp WHERE gcp.connection_id IN ( SELECT connection_id FROM guacamole_connection_permission WHERE entity_id IN ( SELECT entity_id FROM guacamole_user WHERE user_id='SELECT gu.user_id FROM guacamole_user gu WHERE gu.username='mitra_%s' OR gu.username='mitra_frienduser_%s') )", username, username ); // AND connection_id = \'' . mysqli_real_escape_string( $SqlDatabase->_link, $args->args->data->connection ) . '\')"
		void *res = mmgr->mm_Sqllib->Query( mmgr->mm_Sqllib, sqlTmp );
		char **row;
		
		char *username = NULL;
		char *domain = NULL;
		
		DEBUG("[MitraManagerGetUserData] sql ready: %s\n", sqlTmp );
		
		// we must find username
		
		while( ( row = mmgr->mm_Sqllib->FetchRow( mmgr->mm_Sqllib, res ) ) ) 
		{
			if( row[ 0 ] != NULL && row[ 1 ] != NULL )
			{
				if( strcmp( row[ 0 ], "username" ) == 0 )
				{
					if( username == NULL )
					{
						username = StringDuplicate( row[ 1 ] );
					}
				}
				else if( strcmp( row[ 0 ], "domain" ) == 0 )
				{
					if( domain == NULL )
					{
						domain = StringDuplicate( row[ 1 ] );
					}
				}
				
				if( username != NULL && domain != NULL )
				{
					int ulen = strlen( username );
					int len = ulen + strlen( domain );
					retValue = FMalloc( len+2 );
					if( retValue != NULL )
					{
						strcpy( retValue, username );
						retValue[ ulen ] = ',';
						strcpy( &(retValue[ ulen+1 ]), domain );
					}
					
					DEBUG("[MitraManagerGetUserData] user found: %s\n", retValue );
					break;
				}
			}
		}
	}
	DEBUG("[MitraManagerGetUserData] end\n");
	return retValue;
}
