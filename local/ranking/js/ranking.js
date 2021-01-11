var explorer = new Vue({
    el: '#ranking',
    data(){
      return {
        levelspaginate: [],
        marginLeftBaner: 0,
        banerPoint: 1,
        user: {
              name: '',
              levelName: '',
              levelImage: '',
              points: 0,
              percentage: 0,
        },
        levels: [],
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
                .then((response) => {
                    let data = response.data.data;
                    let levels = [];
                    Object.keys(data).forEach(key => {
                        let dataVal = data[key];
                        let name = dataVal.name;
                        let number = dataVal.number;
                        let img = dataVal.img;
                        let pointMin = dataVal.pointMin;
                        let pointMax = dataVal.pointMax;

                        let newElem = {
                            'name': name,
                            'number': number,
                            'img': img,
                            'pointMin': pointMin,
                            'pointMax': pointMax
                        };
                        levels.push(newElem);
                    });
                    this.levels = levels;
                    this.pages = Math.ceil(this.levels.length/6);
                    this.levelspaginate = new Array(this.pages);
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
        prevBaner: function() {
            let marginLeft = 0;
            this.marginLeftBaner += 100;
            if(marginLeft < this.marginLeftBaner) {
                this.marginLeftBaner = (100 * this.cursospaginate.length - 100)*-1;
            }
            if(this.banerPoint == 1) {
                this.banerPoint = this.cursospaginate.length;
            } else {
                this.banerPoint -= 1;
            }
            $('#leves').animate({'margin-left': this.marginLeftBaner+"%"}, 500);
        },
        nextBaner: function() {
            let marginLeft = (100 * this.cursospaginate.length - 100)*-1;
            this.marginLeftBaner -= 100;
            if(marginLeft > this.marginLeftBaner) {
                this.marginLeftBaner = 0;
            }
            if(this.banerPoint <  this.cursospaginate.length) {
                this.banerPoint += 1;
            } else if(this.banerPoint ==  this.cursospaginate.length) {
                this.banerPoint = 1;
            }
            $('#leves').animate({'margin-left': this.marginLeftBaner+"%"}, 500);
        },
    }
});