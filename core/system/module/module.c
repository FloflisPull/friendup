/*©mit**************************************************************************
*                                                                              *
* This file is part of FRIEND UNIFYING PLATFORM.                               *
* Copyright 2014-2017 Friend Software Labs AS                                  *
*                                                                              *
* Permission is hereby granted, free of charge, to any person obtaining a copy *
* of this software and associated documentation files (the "Software"), to     *
* deal in the Software without restriction, including without limitation the   *
* rights to use, copy, modify, merge, publish, distribute, sublicense, and/or  *
* sell copies of the Software, and to permit persons to whom the Software is   *
* furnished to do so, subject to the following conditions:                     *
*                                                                              *
* The above copyright notice and this permission notice shall be included in   *
* all copies or substantial portions of the Software.                          *
*                                                                              *
* This program is distributed in the hope that it will be useful,              *
* but WITHOUT ANY WARRANTY; without even the implied warranty of               *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the                 *
* MIT License for more details.                                                *
*                                                                              *
*****************************************************************************©*/

/** @file
 * 
 *  Module body
 *
 *  @author PS (Pawel Stefanski)
 *  @date created 2015
 */

#include <dlfcn.h>
#include <core/library.h>
#include "module.h"

/**
 * Load and create module
 *
 * @param path path to module on disk
 * @param name name which will be used to recognize module
 * @return new EModule structure when success, otherwise NULL
 */
EModule *EModuleCreate( void *sb, const char *path, const char *name )
{
	EModule *mod = NULL;
	char *suffix = NULL;

	if( name == NULL || strlen( name ) < 4 )
	{
		return NULL;
	}

	suffix =(char *) &(name[ strlen( name )-4 ]);

	DEBUG("Emodule create %s  suffix %s\n", path, suffix );

	if( strcmp( suffix, "emod") != 0 )
	{
		DEBUG("Emodule create suffix %s\n", suffix );
		return NULL;
	}

	if( ( mod = FCalloc( sizeof(EModule), 1 ) ) != NULL )
	{
		if( ( mod->Name = FCalloc( strlen( name )+1, sizeof(char) ) ) != NULL )
		{
			strcpy( mod->Name, name );
		}

		if( ( mod->Path = FCalloc( strlen( path )+1, sizeof(char) ) ) != NULL )
		{
			strcpy( mod->Path, path );
		}

		if( ( mod->handle = dlopen ( path, RTLD_NOW ) ) != NULL )
		{
			mod->Run = dlsym( mod->handle, "Run");
			mod->GetSuffix = dlsym ( mod->handle, "GetSuffix");
		}
		
		mod->em_SB = sb;
	}
	return mod;
}

/**
 * Delete module
 *
 * @param mod pointer to EModule which will be deleted
 */
void EModuleDelete( EModule *mod )
{
	if( mod != NULL )
	{
		if( mod->Name )
		{
			FFree( mod->Name );
		}

		if( mod->Path )
		{
			FFree( mod->Path );
		}

		if( mod->handle )
		{
			dlclose ( mod->handle );
		}

		FFree( mod );
	}

}
