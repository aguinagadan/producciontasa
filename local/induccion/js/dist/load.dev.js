"use strict";

var audio_ambiente = new Audio('audio/ambiental.mp3');
var audio_final = new Audio('audio/ambiental-final.mp3');
var audio_pop1 = new Audio('audio/pop1.mp3');
var audio_pop2 = new Audio('audio/pop1.mp3');
var audio_transition = new Audio('audio/millas-optenidas.mp3');
var app = new Vue({
  el: '#app-mapa',
  data: function data() {
    return {
      millas: 0,
      cursos: [{
        'title': 'Nosotros',
        'status': true
      }, {
        'title': 'Gestión Humana',
        'status': true,
        'active': true,
        'successfull': true
      }, {
        'title': 'Seguridad 1',
        'status': true
      }, {
        'title': 'Seguridad 2',
        'status': true
      }, {
        'title': 'Seguridad 3'
      }],
      millasAct: 0,
      pointAdd: 0
    };
  },
  created: function created() {
    // console.log(this.cursos[0].active);
    this.iniciar(); // this.loadData();

    this.showGame();
  },
  methods: {
    loadData: function loadData() {
      var _this = this;

      console.log("loadData");
      var frm = new FormData();
      frm.append('request_type', 'obtenerCursos');
      axios.post('./ajax_controller_induccion.php', frm).then(function (response) {
        // handle success
        var data = response.data.data;
        var all = Array();
        var activeBoya = false;

        for (var i = 0; i < data.length; i++) {
          var element = data[i];

          if (element.courseFullName.split(" ").length > 1) {
            name = element.courseFullName.split(" ")[0] + ' ' + element.courseFullName.split(" ")[1];
          } else {
            name = element.courseFullName;
          }

          var newElem = {};

          if (i + 1 < data.length) {
            if (data[i + 1].successfull != true && activeBoya == false) {
              newElem = {
                'title': name,
                'status': true,
                'active': true
              };
              activeBoya = true;
              console.log(activeBoya);
            } else {
              newElem = {
                'title': name
              };
            }
          } else {
            if (element.successfull == true) {
              newElem = {
                'title': name,
                'status': true,
                'successfull': true
              };
            } else if (activeBoya == false) {
              newElem = {
                'title': name,
                'status': true,
                'active': true
              };
            } else {
              newElem = {
                'title': name
              };
            }
          }

          all.push(newElem);
        }

        _this.cursos = all;

        _this.showGame();
      })["catch"](function (error) {
        // handle error
        console.log(error);
      });
    },
    showGame: function showGame() {
      var cant = this.cursos.length;
      var styles = "css/item/item_".concat(cant, ".css");
      var newSS = document.createElement('link');
      newSS.rel = 'stylesheet';
      newSS.href = styles;
      document.getElementsByTagName("head")[0].appendChild(newSS); // console.log(this.cursos);

      if (this.cursos[0].active) {
        this.showVideo();
      } else {
        this.showPergamino();
        this.show_back();
      }
    },
    suma: function suma() {
      var _this2 = this;

      // console.log("ingreso a suma");
      var millas = parseInt(this.millas);
      var millasAct = parseInt(this.millasAct);
      var total = millas + millasAct;
      var timeInterval = 1500 / millasAct;
      var time = setInterval(function () {
        _this2.addpoint(_this2.millas, millasAct, total);
      }, timeInterval);
      setTimeout(function () {
        clearInterval(time);
      }, 1500);
    },
    addpoint: function addpoint(millas, millasAct, total) {
      this.millas = millas + 1;
    },
    showPergamino: function showPergamino() {
      // console.log("mostrar pergamino");
      audio_ambiente.play(); // audio.play();

      audio_ambiente.onended = function () {
        audio_ambiente.play();
      }; //transicion.mp3


      var audio2 = new Audio('audio/transicion.mp3');
      audio2.play();
      $('.contanier-game-load').hide();
      $('.container-game').show();
      $('.container-game').fadeIn();
      setTimeout(this.show_cursos, 2000);
    },
    showVideo: function showVideo() {
      var _this3 = this;

      $('.contanier-game-load').fadeIn();
      var vid = document.getElementById("video");
      setTimeout(function () {
        audio_transition.play();
        vid.play();
        $('.contanier-game-load video').fadeIn();
      }, 1500);
      $("#video").on('ended', function () {
        return _this3.showPergamino();
      });
    },
    iniciar: function iniciar() {
      $('.contanier-game-load').hide();
      $('.contanier-game-load video').hide();
      $('.container-game').hide();
      $('.mapa .boya').hide();
      $('.mapa .boatch').hide();
      $('.back-game').hide();
      $('.final').hide();
    },
    show_cursos: function show_cursos() {
      var item = $('.mapa .boya').length;
      var i = 1;
      var time = setInterval(function () {
        if (i % 2 == 0) {
          audio_pop1.play();
        } else {
          audio_pop2.play();
        }

        $('.mapa .boya:nth-child(' + i + ')').show();

        if (i == item) {
          $('.mapa .boatch').fadeIn();
          clearInterval(time);
        }

        i += 1;
      }, 500);
    },
    show_back: function show_back() {
      // console.log("show_back");
      // $('.back-game').fadeIn();
      $('.back-game').show();
      audio_transition.play();
    },
    show_final: function show_final() {
      $('.contanier-game-load').hide();
      $('.container-game').hide();
      $('.mapa .boya').hide();
      $('.mapa .boatch').hide();
      $('.back-game').hide();
      $('.final .text').hide();
      $('.final').show(function () {
        setTimeout(function () {
          $('.final .text').show();
        }, 1500);
      }); // console.log(audio_ambiente);

      audio_ambiente.pause();
      audio_final.play();

      audio_final.onended = function () {
        audio_final.play();
      };
    },
    fade_back: function fade_back() {
      var _this4 = this;

      // console.log("face_back");
      $('.back-game .content').addClass('add');
      setTimeout(function () {
        $('.back-game').hide();

        _this4.suma();

        var cant = _this4.cursos.length - 1; // console.log(this.cursos[cant]);

        if (_this4.cursos[cant].successfull) {
          setTimeout(function () {
            _this4.show_final();
          }, 1200);
        }
      }, 1000);
    }
  }
});