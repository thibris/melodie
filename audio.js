var aud = {
  // (A) INITIALIZE PLAYER
  player : null,   // html <audio> element
  playlist : null, // html playlist
  now : 0,         // current song
  init : () => {
    // (A1) GET HTML ELEMENTS
    aud.player = document.getElementById("demoAudio");
    aud.playlist = document.querySelectorAll("#demoList .song");

    // (A2) LOOP THROUGH ALL THE SONGS, CLICK TO PLAY
    for (let i=0; i<aud.playlist.length; i++) {
      aud.playlist[i].onclick = () => aud.play(i);
    }

    // (A3) AUTO PLAY WHEN SUFFICIENTLY LOADED
    aud.player.oncanplay = aud.player.play;

    // (A4) AUTOPLAY NEXT SONG IN PLAYLIST WHEN CURRENT SONG ENDS
    aud.player.onended = () => {
      aud.now++;
      if (aud.now>=aud.playlist.length) { aud.now = 0; }
      aud.play(aud.now);
    };
  },

  // (B) PLAY SELECTED SONG
  play : id => {
    // (B1) UPDATE AUDIO SRC
    aud.now = id;
    aud.player.src = "" + aud.playlist[id].dataset.src;

    // (B2) A LITTLE BIT OF COSMETIC
    for (let i=0; i<aud.playlist.length; i++) {
      if (i==id) { aud.playlist[i].classList.add("now"); }
      else { aud.playlist[i].classList.remove("now"); }
    }
  }
};
window.addEventListener("DOMContentLoaded", aud.init);
