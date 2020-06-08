/*©agpl*************************************************************************
*                                                                              *
* This file is part of FRIEND UNIFYING PLATFORM.                               *
* Copyright (c) Friend Software Labs AS. All rights reserved.                  *
*                                                                              *
* Licensed under the Source EULA. Please refer to the copy of the GNU Affero   *
* General Public License, found in the file license_agpl.txt.                  *
*                                                                              *
*****************************************************************************©*/

var pausebtn, playbtn;

// Initialize the GUI ----------------------------------------------------------
Application.run = function( msg, iface )
{
	this.song = false;
	pausebtn = ge( 'pausebutton' );
	this.miniplaylist = false;
	playbtn  = ge( 'playbutton'  );
	this.playlist = [];
	
	this.volume = 64;
	
	CreateSlider( ge( 'Volume' ), {
		type: 'volume'
	} );
	
	ge( 'scroll' ).innerHTML = i18n( 'i18n_empty_playlist' );
	
	if( window.isMobile )
	{
		Application.receiveMessage( {
			command: 'miniplaylist',
			visibility: true
		} );
	}
	
	this.clearVisualizer( 50 );
}

Application.setVolume = function( vol )
{
	if( this.song )
		this.song.setVolume( vol / 255 );
}

Application.redrawMiniPlaylist = function()
{
	var playlist = this.playlist;
	var index = this.index;
	
	if( !playlist )
	{
		this.sendMessage( {
			command: 'get_playlist'
		} );
	}
	else
	{
		var sw = 2;
		var tb = document.createElement( 'div' );
		tb.className = 'List NoPadding';
		for( var a = 0; a < playlist.length; a++ )
		{
			var tr = document.createElement( 'div' );
			var sanitize = playlist[a].Filename;
			if( sanitize.indexOf( '.' ) > 0 )
			{
				sanitize = sanitize.split( '.' );
				sanitize.pop();
				sanitize = sanitize.join( '.' );
			}
			tr.innerHTML = sanitize;
			sw = sw == 1 ? 2 : 1;
			var c = '';
			if( a == index )
			{
				c = ' Selected Playing';
			}
			tr.className = 'Tune Padding Ellipsis sw' + sw + c;
			( function( ele, eles, index )
			{
				tr.onclick = function()
				{
					for( var u = 0; u < eles.childNodes.length; u++ )
					{
						if( eles.childNodes[u] != ele && eles.childNodes[u].classList )
						{
							eles.childNodes[u].classList.remove( 'Playing' );
							eles.childNodes[u].classList.remove( 'Selected' );
						}
					}
					ele.classList.add( 'Playing', 'Selected' );
					if( Application.song )
					{
						Application.song.stop();
					}
					Application.sendMessage( { command: 'playsongindex', index: index } );
				}
			} )( tr, tb, a );
			tb.appendChild( tr );
		}
		if( playlist.length > 0 )
		{
			ge( 'MiniPlaylist' ).innerHTML = '';
			ge( 'MiniPlaylist' ).appendChild( tb );
			ge( 'MiniPlaylist' ).style.bottom = '45px';
			ge( 'MiniPlaylist' ).style.height = GetElementHeight( tb );
		}
		else
		{
			ge( 'MiniPlaylist' ).innerHTML = '<div class="List"><div class="sw1">Playlist is empty.</div></div>';
		}
		var h = GetElementHeight( ge( 'visualizer' ) );
		h += GetElementHeight( tb );
		h += GetElementHeight( ge( 'BottomButtons' ) );
		if( h > 300 ) h = 300;
		Application.sendMessage( { command: 'resizemainwindow', size: h } );
	}
}

Application.receiveMessage = function( msg )
{
	if( !msg.command ) return;
	
	var bsrc = '/system.library/file/read?authid=' + 
		Application.authId + '&mode=rb';
	
	switch( msg.command )
	{
		case 'updateplaylist':
			this.index = msg.index;
			this.playlist = msg.playlist;
			if( this.miniplaylist )
			{
				this.redrawMiniPlaylist();
			}
			break;
		case 'toggle_miniplaylist':
			this.miniplaylist = this.miniplaylist ? false : true;
			break;
		case 'miniplaylist':
			// Could be we're asked to toggle visibility
			if( msg.visibility ) this.miniplaylist = msg.visibility;
		
			if( this.miniplaylist )
			{
				ge( 'Equalizer' ).style.height = '113px';
				ge( 'MiniPlaylist' ).style.bottom = '47px';
				ge( 'MiniPlaylist' ).style.top = '113px';
				ge( 'MiniPlaylist' ).style.visibility = 'visible';
				ge( 'MiniPlaylist' ).style.inputEvents = '';
				ge( 'MiniPlaylist' ).style.opacity = 1;
				this.index = msg.index;
				this.playlist = msg.playlist;
				this.redrawMiniPlaylist();
			}
			else
			{
				ge( 'Equalizer' ).style.height = 'auto';
				ge( 'Equalizer' ).style.bottom = '47px';
				ge( 'MiniPlaylist' ).style.bottom = '';
				ge( 'MiniPlaylist' ).style.top = 'auto';
				ge( 'MiniPlaylist' ).style.visibility = 'hidden';
				ge( 'MiniPlaylist' ).style.opacity = 0;
				ge( 'MiniPlaylist' ).style.inputEvents = 'none';
			}
			break;
		case 'play':
			if( !msg.item ) return;
			var self = this;
			//var src = '/system.library/file/read?mode=r&readraw=1' +
			//	'&authid=' + Application.authId + '&path=' + msg.item.Path;
			ge( 'progress' ).style.opacity = 0;
			ge( 'scroll' ).innerHTML = '<div>' + i18n( 'i18n_loading_song' ) + '...</div>';
			if( this.song ) 
			{
				var tmp = this.song.onfinished;
				this.song.onfinished = function(){};
				this.song.stop();
				this.song.unload();
			}
			this.song = new AudioObject( msg.item.Path, function( result, err )
			{
				if( !result )
				{
					if( err )
					{
						return;
					}
					// Try the next song
					setTimeout( function()
					{
						Seek( 1 );
					}, 500 );
				}
				else
				{
					// No error
					if( !err )
					{
						self.song.setVolume( ge( 'Volume' ).value / 255 );
					}
				}
			} );
			if( this.miniplaylist )
			{
				var eles = ge( 'MiniPlaylist' ).getElementsByClassName( 'Ellipsis' );
				for( var a = 0; a < eles.length; a++ )
				{
					if( a == msg.index )
					{
						eles[a].classList.add( 'Selected', 'Playing' );
					}
					else
					{
						eles[a].classList.remove( 'Selected', 'Playing' );
					}
				}
				this.index = msg.index;
			}
			this.song.onload = function()
			{
				document.body.classList.remove( 'Paused' );
				document.body.classList.add( 'Playing' );
				
				Application.clearVisualizer( 50 );
				
				this.play();
				
				Application.initVisualizer();
				var fn = msg.item.Filename;
				
				var cand = '';
				if( this.loader && this.loader.metadata )
				{
					let md = this.loader.metadata;
					
					if( typeof md.title != undefined && md.title != 'undefined' )
					{
						cand += md.title;
					}
					if( typeof md.artist != undefined && md.artist != 'undefined' )
					{
						cand += ' by ' + md.artist;
					}
					if( typeof md.album != undefined || typeof md.year != undefined )
					{
						cand += ' (';
						if( typeof md.album != undefined && md.album != 'undefined' )
						{
							cand += md.album;
							if( typeof md.year != undefined && md.year != 'undefined' )
								cand += ', ';
						}
						if( typeof md.year != undefined && md.year != 'undefined' )
						{
							cand += md.year;
						}
						cand += ')';
					}
					fn = cand;
				}
				
				if( fn == false ) return;
				ge( 'scroll' ).innerHTML = '<div>' + fn + '</div>';
			}
			this.song.onfinished = function()
			{
				Seek( 1 );
			}
			this.song.ct = -1;
			this.song.onplaying = function( progress, ct, pt, dr )
			{	
				var seconds = Math.round( ct - pt ) % 60;
				
				var timeFin = dr + ( pt - ct );
				var finMins = Math.floor( timeFin / 60 );
				var finSecs = Math.floor( timeFin ) % 60;
				
				if( this.ct != seconds )
				{
					ge( 'progress' ).style.width = ( Math.floor( progress * 10000 ) / 100 ) + '%';
					ge( 'progress' ).style.opacity = 1;
					
					this.ct = seconds;

					if( finMins < 0 )
					{
						Seek( 1 );
						ge( 'time' ).innerHTML = '0:00';
						return;
					}
					ge( 'time' ).innerHTML = finMins + ':' + StrPad( finSecs, 2, '0' );
				}
			}
			pausebtn.className = pausebtn.className.split( 
				' active' ).join( '' );
			playbtn.classList.remove( 'fa-play' );
			playbtn.classList.add( 'fa-refresh' );
			playbtn.className  = playbtn.className.split( 
				' active' ).join( '' ) + ' active';
			break;
		case 'pause':
			var s = this.song;
			if( !s ) return false;
			s.pause();
			
			document.body.classList.add( 'Paused' );
			document.body.classList.remove( 'Playing' );
			
			if( s.paused )
			{
				pausebtn.className = pausebtn.className.split( 
					' active' ).join( '' ) + ' active';
				playbtn.className  = playbtn.className.split( 
					' active' ).join( '' ) + ' active';
			}
			else
			{
				// Connect to the source
				Application.initVisualizer();
				pausebtn.className = pausebtn.className.split( 
					' active' ).join( '' );
			}
			break;
		case 'stop':
			var s = this.song;
			if( !s ) return false;
			
			document.body.classList.remove( 'Paused' );
			document.body.classList.remove( 'Playing' );
			
			s.stop();
			
			Application.clearVisualizer( 50 );
			
			pausebtn.className = pausebtn.className.split( 
				' active' ).join( '' );
			playbtn.classList.remove( 'fa-refresh' );
			playbtn.classList.add( 'fa-play' );
			playbtn.className  = playbtn.className.split( 
				' active' ).join( '' );
			ge( 'progress' ).style.opacity = 1;
			break;
		case 'drop':
			if( msg.data )
			{
				var items = [];
				for( var a in msg.data )
				{
					items.push( {
						Path: msg.data[a].Path, 
						Filename: msg.data[a].Filename
					} );
				}
				Application.sendMessage( {
					command: 'append_to_playlist_and_play',
					items: items
				} );
			}
			break;
	}
}

Application.clearVisualizer = function( time )
{
	let eq = ge( 'visualizer' ); let w = eq.offsetWidth, h = eq.offsetHeight;
	eq.setAttribute( 'width', w ); eq.setAttribute( 'height', h );

	function f()
	{
		let ctx = eq.getContext( '2d' );
		
		let grd = ctx.createLinearGradient( 0, 0, 0, h );
		grd.addColorStop( 0, '#150423' );
		grd.addColorStop( 0.5, '#362544' );
		grd.addColorStop( 1, '#150423' );
		
		ctx.fillStyle = grd;
		ctx.fillRect( 0, 0, w, h );
	}

	if( time )
	{
		setTimeout( function()
		{
			f();
		}, time );
	}
	else f();
}

// Initialize player!!!
Application.initVisualizer = function()
{	
	let eq = ge( 'visualizer' ); let w = eq.offsetWidth, h = eq.offsetHeight;
	eq.setAttribute( 'width', w ); eq.setAttribute( 'height', h );
	
	eq.onclick = function( e )
	{
		if( this.fullscreenEnabled )
		{
			this.classList.remove( 'Fullscreen' );
		}
		else
		{
			this.classList.add( 'Fullscreen' );
		}
		Application.fullscreen( this );
		
	}
	
	let act = this.song.getContext();
	let ana = act.createAnalyser(); ana.fftSize = 2048;
	let bufLength = ana.frequencyBinCount;
	let dataArray = new Uint8Array( bufLength );
	let px = 0, py = 0, bx = 0, sw = 0;
	
	let ctx = eq.getContext( '2d' );
	
	this.clearVisualizer();
	
	// Connect to the source
	let agr = this.song.loader.audioGraph;
	let src = agr.source;
	src.connect( ana );
	ana.connect( agr.context.destination );
	
	// Run it!
	
	let scroll = ge( 'scroll' );
	let scrollPos = 0;
	let scrollDir = -1;
	let waitTime = 0;
	
	this.dr = function()
	{
		ana.getByteTimeDomainData( dataArray );
		
		// Blur
		let y = 0, x = 0, off = 0, w4 = w << 2;
		let d = ctx.getImageData( 0, 0, w, h );
		let hy = 0;
		let h2 = h >> 1;
		for( y = 0; y < h; y++ )
		{
			hy = y < h2 ? y : ( h - y );
			for( x = 0; x < w4; x += 4 )
			{
				d.data[ off ] -= ( d.data[ off++ ] - 20 - hy ) >> 1;
				d.data[ off ] -= ( d.data[ off++ ] - 3 - hy ) >> 1;
				d.data[ off ] -= ( d.data[ off++ ] - 34 - hy ) >> 1;
				off++;
			}
		}
		ctx.putImageData( d, 0, 0 );
		ctx.strokeStyle = '#C48EFF';
		ctx.lineWidth = 2;
		ctx.beginPath();
		let hh = h >> 1;
		let sw = 1 / bufLength * w;
		
		// TODO: If amplitude is high, flash!
		// TODO: Other visualizations
		for( bx = 0, px = 0; bx < bufLength; bx++, px += sw )
		{
			py = dataArray[ bx ] / 128.0 * hh;
			
			if( px == 0 )
			{
				ctx.moveTo( px, py );
			}
			else
			{
				ctx.lineTo( px, py );
			}
		}
		ctx.stroke();
		
		// Only do this when not stopped..
		if( agr.started )
		{
			requestAnimationFrame( Application.dr );
		}
		
		if( scroll.firstChild && waitTime == 0 )
		{
			let scrollW = scroll.offsetWidth - 60;
			let elemenW = scroll.firstChild.offsetWidth;
			if( elemenW > scrollW )
			{
				if( scrollDir < 0 )
				{
					scrollPos -= 0.25;
				
					if( elemenW + scrollPos <= scrollW )
					{
						scrollDir = 1;
						waitTime = 1000;
					}
				}
				else
				{
					scrollPos += 0.25;
					
					if( scrollPos >= 0 )
					{
						scrollDir = -1;
						waitTime = 1000;
					}
				}
				scroll.firstChild.style.left = parseInt( scrollPos ) + 'px';
			}
		}
		if( waitTime > 0 ) waitTime--;
	};
	requestAnimationFrame( Application.dr );
	
}

function PlaySong()
{
	ge( 'player' ).src = '';
	Application.sendMessage( { command: 'playsong' } );
}

function PauseSong()
{
	Application.receiveMessage( { command: 'pause' } );
}

function StopSong()
{
	Application.receiveMessage( { command: 'stop' } );
}

function Seek( direction )
{
	if( Application.song.paused || Application.song.stopped )
		return;
	StopSong();
	Application.sendMessage( { command: 'seek', dir: direction } ); 
}

function EditPlaylist()
{
	Application.sendMessage( { command: 'edit_playlist' } );
}

