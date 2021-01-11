var explorer = new Vue({
    el: '#ranking',
    data(){
      return{
        levels: [
            {
                name: "Fitoplancton",
                level: 1,
                img: "img/delfin.png",
                pointMin: 0,
                pointMax: 199
            },
            {
                name: "Zooplancton",
                level: 2,
                img: "img/delfin.png",
                pointMin: 200,
                pointMax: 249
            },
            {
                name: "Jurel",
                level: 3,
                img: "img/delfin.png",
                pointMin: 250,
                pointMax: 349
            },
            {
                name: "Sardina",
                level: 4,
                img: "img/delfin.png",
                pointMin: 350,
                pointMax: 499
            },
            {
                name: "Bonito",
                level: 5,
                img: "img/delfin.png",
                pointMin: 500,
                pointMax: 799
            },
            {
                name: "Tortuga marina",
                level: 6,
                img: "img/delfin.png",
                pointMin: 800,
                pointMax: 1049
            },
        ],
        user: {
            name: "Juan Carlos Matias Lorenzo",
            level: "Sardina",
            point: 352,
            porcent: 20,
        },
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
      }         
    },
    created(){
    //   this.sizeWeb();
    //   window.onresize = this.sizeWeb;
      this.size();
    },
    mounted(){
      
    },
    methods: {
    //   sizeWeb: function(){
    //     if (window.innerWidth < 768)
    //       this.menu = false;
    //     else
    //       this.menu = true;
    //   },
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