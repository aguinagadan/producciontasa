var explorer = new Vue({
    el: '#ranking',
    data(){
      return{
        user: {
              name: '',
              levelName: '',
              levelImage: '',
              points: 0,
              percentage: 0,
        },
        levels: [
            {
                name: "Fitoplancton",
                level: 1,
                img: "img/delfin.png",
                pointMin: 0,
                pointMax: 199
            }
        ],
        area: [
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
            {name: "Operaciones", punto: "2,352 millas náuticas"},
        ],
        users: [
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
            {name: "Yasmin Liseth Castañeda Calderon", punto:"2,352 millas náuticas", level: "Nivel 10, Anchoveta"},
        ],
        positionCard: 0,
        widthContent: 0,
        maxMov: 0,
        time: '',
      };
    },
    created(){
    //   this.sizeWeb();
    //   window.onresize = this.sizeWeb;
      this.size();
    },
    mounted(){
        this.obtenerUsuario();
        this.obtenerNiveles();
    },
    methods: {
        obtenerUsuario: function(){
            let frm = new FormData();
            frm.append('request_type','obtenerUsuario');
            axios.post('/local/ranking/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    this.user.id = data.id;
                    this.user.name = data.name;
                    this.user.levelName = data.levelName;
                    this.user.levelImage = data.levelImage;
                    this.user.points = data.points;
                    this.user.percentage = data.percentage;
                });
        },
        obtenerNiveles: function(){
            let frm = new FormData();
            frm.append('request_type','obtenerNiveles');
            axios.post('/local/ranking/ajax_controller.php',frm)
                .then(() => {
                    console.log('test');
                });
        },
        size: function(){
            this.widthContent = this.area.length*210;
            console.log(this.widthContent);
            document.querySelector("#leves").style.width = this.widthContent+"px";
            let container = document.querySelector(".niveles .body").clientWidth - 20;
            this.maxMov = container - this.widthContent;
            console.log(this.maxMov);
        },
        movR: function(){
            this.time = setInterval(() => {
                if(this.positionCard > this.maxMov){
                    this.positionCard -= 1;
                } else{
                    removeMovR();
                }
            }, 20);
        },
        removeMovR: function(){
            clearInterval(this.time);
        }
    }
});