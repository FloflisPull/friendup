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
 *  Http File structures and functions body
 *
 *  @author PS
 *  @date created 26/03/2020
 */

#include <core/types.h>
#include <time.h>
#include <fcntl.h>
#include <errno.h>
#include <sys/mman.h>
#include "http_file.h"
#include "http.h"

/**
 * Create new HttpFile
 *
 * @param filename file name of new file
 * @param fnamesize file name string length
 * @param data pointer to file data
 * @param size size of provided data
 * @return HttpFile or NULL when error appear
 */

HttpFile *HttpFileNew( char *filename, int fnamesize, char *data, FQUAD size )
{
	if( size <= 0 )
	{
		FERROR("Cannot upload empty file\n");
		return NULL;
	}
	
	if( size > (10 *1024 *1024) )
	{
		
	}
	
	HttpFile *file = FCalloc( 1, sizeof( HttpFile ) );
	if( file == NULL )
	{
		FERROR("Cannot allocate memory for HTTP file\n");
		return NULL;
	}
	
	file->hf_FileHandle = -1;
	
	DEBUG("[HttpFileNew] File will be created, size: %ld\n", size );
	if( size > TUNABLE_LARGE_HTTP_REQUEST_SIZE )
	{
		strcpy( file->hf_FileNameOnDisk, "/tmp/Friendup/Friend_File_XXXXXX" );

		//this is going to be a huge request, create a temporary file
		//copy already received data to it and continue writing to the file
		char *tmpFilename = mktemp( file->hf_FileNameOnDisk );
		//DEBUG( "large upload will go to remporary file %s", tmp_filename );
		if( strlen( tmpFilename ) == 0 )
		{
			FERROR("mktemp failed!");
			HttpFileDelete( file );
			return NULL;
		}
		
		file->hf_FileHandle = open( tmpFilename, O_RDWR | O_CREAT | O_EXCL, 0600/*permissions*/);
		if( file->hf_FileHandle == -1 )
		{
			FERROR("temporary file open failed!");
			HttpFileDelete( file );
			return NULL;
		}
		
		file->hf_Data = mmap( 0, size, PROT_READ | PROT_WRITE, MAP_SHARED, file->hf_FileHandle, 0/*offset*/);
		
		//write already received chunk
		FQUAD toWrite = size;
		char *dataptr = data;
		
		int store = TUNABLE_LARGE_HTTP_REQUEST_SIZE;
		
		DEBUG("[HttpFileNew] Store file\n");
		while( toWrite > 0 )
		{
			int wrote = write( file->hf_FileHandle, dataptr, store );
			dataptr += wrote;
			toWrite -= wrote;
			DEBUG("[HttpFileNew] Store: %d ToWrite: %ld\n", store, toWrite );
			
			if( toWrite < (FQUAD)store )
			{
				store = toWrite;
			}
		}
		DEBUG("[HttpFileNew] Store file END\n");
	}
	else
	{
		char *locdata = FCalloc( size, sizeof( char ) );
		if( locdata == NULL )
		{
			FERROR("Cannot allocate memory for HTTP file data\n");
			return NULL;
		}
		memcpy( locdata, data, size );
		file->hf_Data = locdata;
	}
	
	strncpy( file->hf_FileName, filename, fnamesize );
	file->hf_FileSize = size;
	
	INFO("New file created %s size %lu\n", file->hf_FileName, file->hf_FileSize );
	
	return file;
}

/**
 * Delete Http File
 *
 * @param f pointer to HttpFile
 */

void HttpFileDelete( HttpFile *f )
{
	if( f != NULL )
	{
		if( f->hf_FileHandle >= 0 )
		{
			if( f->hf_Data )
			{
				munmap( f->hf_Data, f->hf_FileSize );
			}
			close( f->hf_FileHandle );
			unlink( f->hf_FileNameOnDisk );
		}
		else
		{
			if( f->hf_Data != NULL )
			{
				FFree( f->hf_Data );
			}
		}
		
		FFree( f );
	}
}