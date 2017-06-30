'use strict';
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



const log = require( './Log' )( 'Session' );
const Emitter = require( './Events' ).Emitter;
const util = require( 'util' );

const ns = {};
ns.Session = function( id, onclose ) {
	const self = this;
	self.id = id;
	self.onclose = onclose;
	
	self.sessionTimeout = 1000 * 60;
	self.sessionTimer = null;
	self.connections = {};
	self.connIds = [];
	
	self.isPublic = false;
	self.subscribers = [];
	self.meta = {};
	
	Emitter.call( self );
	
	self.init();
}

util.inherits( ns.Session, Emitter );

// Public

// system attaches a new client connection
ns.Session.prototype.attach = function( conn ) {
	const self = this;
	log( 'attach', conn.id );
	if ( !conn )
		return;
	
	if ( self.sessionTimer ) {
		clearTimeout( self.sessionTimer );
		self.sessionTimer = null;
	}
	
	const cid = conn.id;
	self.connections[ cid ] = conn;
	self.connIds.push( cid );
	conn.on( 'event', handleEvent );
	conn.setSession( self.id );
	
	function handleEvent( e ) { self.handleEvent( e, cid ); }
}

// system detaches a ( most likely closed ) client connection
ns.Session.prototype.detach = function( cid, callback ) {
	const self = this;
	log( 'detach', cid );
	const conn = self.connections[ cid ];
	if ( !conn ) {
		if ( callback )
			callback( null );
		return;
	}
	
	conn.unsetSession( setBack );
	function setBack() {
		conn.release( 'event' );
		delete self.connections[ cid ];
		self.connIds = Object.keys( self.connections );
		if ( !self.checkConnsTimeout )
			self.checkConnsTimeout = setTimeout( checkConns, 1000 );
		
		if ( callback )
			callback( conn );
	}
	
	function checkConns() {
		self.checkConns();
	}
}

ns.Session.prototype.updateMeta = function( conf ) {
	const self = this;
	log( 'updateMeta', conf );
	if ( !conf )
		return;
	
	if ( 'string' === typeof( conf.name ))
		self.meta.name = conf.name;
	
	if ( 'string' === typeof( conf.description ))
		self.meta.description = conf.description;
	
	if ( conf.apps && conf.apps.forEach )
		self.meta.apps = conf.apps;
	
	if ( 'string' === typeof( conf.imagePath ))
		self.meta.imagePath = conf.imagePath;
}

ns.Session.prototype.exposeApps = function( apps ) {
	const self = this;
	if ( !apps || apps.forEach )
		return null;
	
	if ( !self.meta.apps || !self.meta.apps.forEach )
		self.meta.apps = [];
	
	const parsed = apps.map( parse );
	parsed = parsed.filter( item => !!item );
	self.meta.apps = self.meta.apps.concat( parsed );
	return self.meta.apps;
	
	function parse( item ) {
		if ( !item.id || !( 'string' === typeof( item.id )) )
			return null;
		
		let id = item.id;
		let name = '';
		let desc = '';
		if ( item.name && item.name.toString )
			name = item.name.toString();
		
		if ( item.description && item.description.toString )
			desc = item.description;
		
		return {
			id          : id,
			name        : name,
			description : desc,
		}
	}
}

ns.Session.prototype.concealApps = function( appIds ) {
	const self = this;
	if ( !appIds || !appIds.forEach )
		return null;
	
	if ( !self.meta.apps || !self.meta.apps.forEach ) {
		self.meta.apps = [];
		return self.meta.apps;
	}
	
	self.meta.apps = self.meta.apps.filter( notInAppIds );
	return self.meta.apps;
	
	function notInAppIds( item ) {
		let is = -1;
		is = appIds.indexOf( item.id );
		if ( -1 !== is )
			return false;
		else
			return true;
	}
}

// sends events to client(s), clientId is optional
ns.Session.prototype.send = function( event, clientId, callback ) {
	const self = this;
	
	if ( null != clientId )
		self.sendOnConn( event, clientId, callback );
	else
		self.broadcast( event, callback );
}

// closes session, either from account( logout ), from lack of client connections
// or from nomansland for whatever reason
ns.Session.prototype.close = function() {
	log( 'close' );
	const self = this;
	if ( self.checkConnsTimeout )
		clearTimeout( self.checkConnsTimeout );
	
	if ( self.sessionTimer ) {
		clearTimeout( self.sessionTimer );
		self.sessionTimer = null;
	}
	
	const onclose = self.onclose;
	delete self.onclose;
	
	self.emitterClose();
	self.clearConns();
	
	if ( onclose )
		onclose();
}

// Private

ns.Session.prototype.init = function() {
	const self = this;
	log( 'init ' );
}

ns.Session.prototype.handleEvent = function( event, clientId ) {
	const self = this;
	log( 'handleEvent', event );
	self.emit(
		event.type,
		event.data,
		clientId
	);
}

ns.Session.prototype.broadcast = function( event, callback ) {
	const self = this;
	const lastIndex = ( self.connIds.length -1 );
	self.connIds.forEach( sendTo );
	function sendTo( cid, index ) {
		if ( index === lastIndex )
			self.sendOnConn( event, cid, callback );
		else
			self.sendOnConn( event, cid );
	}
}

ns.Session.prototype.sendOnConn = function( event, cid, callback ) {
	const self = this;
	const conn = self.connections[ cid ];
	if ( !conn ) {
		log( 'no conn for id', {
			cid   : cid,
			conns : self.connections }, 3 );
		if ( callback )
			callback();
		return;
	}
	
	conn.send( event, null, callback );
}

ns.Session.prototype.checkConns = function() {
	const self = this;
	self.checkConnsTimeout = null;
	if ( self.connIds.length )
		return;
	
	self.sessionTimer = setTimeout( sessionTimedOut, self.sessionTimeout );
	function sessionTimedOut() {
		self.sessionTimer = null;
		self.close();
	}
}

ns.Session.prototype.clearConns = function() {
	const self = this;
	self.connIds.forEach( unsetSession );
	self.connections = {};
	self.connIds = [];
	
	function unsetSession( cid ) {
		const conn = self.connections[ cid ];
		if ( !conn )
			return;
		
		conn.unsetSession();
	}
}

module.exports = ns.Session;
