var audio_ambiente = new Audio('audio/ambiental.mp3');
var audio_final = new Audio('audio/ambiental-final.mp3');
var audio_pop1 = new Audio('audio/pop1.mp3');
var audio_pop2 = new Audio('audio/pop1.mp3');
var audio_transition = new Audio('audio/millas-optenidas.mp3');
var app = new Vue({
    el: '#app-mapa',
    data(){ return{
        millas: 0,
        cursos: [
            {
                'title':'Nosotros',
                'status': true,
                // 'active': true,
                'URL': "http://google.com.pe",
                // 'active': true,
            },
            {
                'title': 'Gestión Humana',
                'status': true,
                'active': true,
            },
            // {
            //     'title': 'Seguridad 1',
            //     'status': true,
            //     'active': true,
            // },
            // {
            //     'title': 'Seguridad 2',
            // },
            // {
            //     'title': 'Seguridad 3',
            // },
            // {
            //     'title': 'Seguridad 3',
            // },
            // {
            //     'title': 'Seguridad 3',
            // },
            // {
            //     'title': 'Seguridad 3',
            // },
        ],
        millasAct: 0,
        pointAdd: 0
    }},
    created (){
        // console.log(this.cursos[0].active);
        this.iniciar();
        this.loadData();
        // this.showGame();
        
    },
    methods:{
        loadData: function(){
            console.log("loadData");
            let frm = new FormData();
            frm.append('request_type','obtenerCursos');
            axios.post('./ajax_controller_induccion.php',frm)
                .then((response) => {
                    // handle success
                    let data = response.data.data;
                    let all = Array();
                    var activeBoya = false;
                    for (let i = 0; i < data.length; i++) {
                        const element = data[i];
                        if(element.courseFullName.split(" ").length > 1){
                            name = element.courseFullName.split(" ")[0]+' '+element.courseFullName.split(" ")[1];
                        } else{
                            name = element.courseFullName;
                        }
                        let newElem = {};
                        
                        if(i+1 < data.length){
                            console.log(data[i+1].successfull+" - "+activeBoya);
                            if(element.successfull == true && activeBoya == false){
                                newElem =  {'title': name,'status': true, 'URL': element.URL};
                            } else if(activeBoya == false){
                                // if(data[i].successfull == true){
                                //     newElem =  {'title': name,'status': true, 'URL': element.URL};
                                // } else{
                                    newElem =  {'title': name,'status': true, 'active': true, 'URL': element.URL};
                                    activeBoya = true;
                                // }
                            } else{ 
                                newElem =  {'title': name};
                            }
                        } else{
                            if(element.successfull == true){
                                newElem =  {'title': name,'status': true, 'successfull': true, 'URL': element.URL};
                            } else if(activeBoya == false){
                                newElem =  {'title': name,'status': true, 'active': true, 'URL': element.URL};
                            } else{
                                newElem =  {'title': name};
                            }
                        }
                        // newElem.URL = element.URL;
                        all.push(newElem);
                    }
                    this.cursos = all;
                    this.showGame();
                    this.millasAct = response.data.millas;
                    this.pointAdd = response.data.millas;
                    console.log(this.cursos);
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                })
        },
        showGame: function(){
            let cant = this.cursos.length;
            var styles = `css/item/item_${cant}.css`;
            var newSS = document.createElement('link');
            newSS.rel = 'stylesheet';
            newSS.href = styles;
            document.getElementsByTagName("head")[0].appendChild(newSS);
            // console.log(this.cursos);
            if(this.cursos[0].active){
                this.showVideo();
            } else{
                this.showPergamino();
                this.show_back();
            }
            var md = window.matchMedia("(max-width: 1024px)");
            var sm = window.matchMedia("(max-width: 1024px) and (max-height: 480px)");
            var smV = window.matchMedia("(max-width: 480px)");
            this.tabled(md, cant);
            this.movileH(sm, cant)
            this.movileV(smV, cant);
            window.addEventListener("orientationchange", ()=> {
                // console.log(window.screen.orientation);
                location.reload();
                // console.log(sm);
                md.addListener(this.tabled(md, cant));
                sm.addListener(this.movileH(sm, cant));
                smV.addListener(this.movileV(smV, cant));
            });
            
        },
        suma: function(){
            // console.log("ingreso a suma");
            let millas = parseInt(this.millas);
            let millasAct = parseInt(this.millasAct);
            let total = millas + millasAct;

            let timeInterval = 1500/millasAct;
            
            var time = setInterval(() => {
                this.addpoint(this.millas,millasAct, total);
            }, timeInterval);
            setTimeout(function () {
                clearInterval(time);
            },1500);    
        },
        addpoint:function(millas, millasAct, total){
            if(this.millas < this.millasAct){
                this.millas = millas + 1;
            }
        },
        showPergamino: function () {
            // console.log("mostrar pergamino");
            audio_ambiente.play();
            // audio.play();
            audio_ambiente.onended = function () {
                audio_ambiente.play();
            }
            //transicion.mp3
            var audio2 = new Audio('audio/transicion.mp3');
            audio2.play();
            $('.contanier-game-load').hide();
            $('.container-game').show();
            $('.container-game').fadeIn();
            setTimeout(this.show_cursos, 2000);
        },
        showVideo: function () {
            $('.contanier-game-load').fadeIn();
            $('.contanier-game-load').css('opacity','1');
            let vid = document.getElementById("video");
            setTimeout(() => {
                audio_transition.play();
                vid.play();
                $('.contanier-game-load video').fadeIn();
            }, 1500);
            $("#video").on('ended', ()=>this.showPergamino());
        },
        iniciar:function(){
            $('.contanier-game-load').hide();
            $('.contanier-game-load video').hide();
            $('.container-game').hide();
            $('.mapa .boya').hide();
            $('.mapa .boatch').hide();
            $('.back-game').hide();
            $('.final').hide();
        },
        show_cursos: function(){
            let item = $('.mapa.v-pc .boya').length;
            let i = 1;
            var time = setInterval(function () {
                if(i % 2 == 0){
                    audio_pop1.play();
                } else{
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
        show_back: function() {
            // console.log("show_back");
            // $('.back-game').fadeIn();
            $('.back-game').show()
            audio_transition.play();
        },
        show_final: function () {
            $('.contanier-game-load').hide();
            $('.container-game').hide();
            $('.mapa .boya').hide();
            $('.mapa .boatch').hide();
            $('.back-game').hide();
            $('.final .text').hide();
            $('.final').show(function () {
                setTimeout(() => {
                    $('.final .text').show();
                }, 1500);
            });
            // console.log(audio_ambiente);
            audio_ambiente.pause();
            audio_final.play();
            audio_final.onended = function () {
                audio_final.play();
            }
        },
        fade_back: function() {
            // console.log("face_back");
            $('.back-game .content').addClass('add');
            setTimeout(()=>{
                $('.back-game').hide();
                this.suma();
                let cant = this.cursos.length - 1;
                // console.log(this.cursos[cant]);
                if (this.cursos[cant].successfull){
                    setTimeout(() => {
                        this.show_final();
                    }, 1200);
                }
            }, 1000);
        },
        tabled: function(x, cant){
            if(x.matches == true){
                console.log("tabled");
                let anch = $('.mapa.v-m').width();
                let alto = anch*2.9423*(0.13+0.0588+((cant-1)*0.1137));
                $('.mapa.v-m .paper').height(alto);
            }
        },
        movileV: function(x, cant){
            if(x.matches == true){
                console.log("tabled");
                let anch = $('.mapa.v-m').width();
                let alto = anch*4*(0.13+0.0588+((cant-1)*0.1137));
                $('.mapa.v-m .paper').height(alto);
            }
        },
        movileH: function(x, cant){
            if(x.matches == true){
                console.log("movil");
                $('.mapa.v-m .paper').height('100%');
                let alto = $('.mapa.v-m').height();
                let anch = alto*4.5*(0.10+0.0588+((cant-1)*0.13));
                if($('.mapa.v-m .paper').width < anch){
                    $('.mapa.v-m .paper').width(anch);
                } else{
                    $('.mapa.v-m .paper').width('100%');
                }
            }
        }
    }
})



function checkPosition(cant)
{
    console.log(cant);   
    if($(window).width() < 767)
    {
       
    } else {
        
    }
}