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
        users: [],
        areas: [],
        positionCard: 0,
        widthContent: 0,
        maxMov: 0,
        time: '',
        nextBanerCount: 1,
        prevBanerCount: 1
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
        this.obtenerUsuarios();
        this.obtenerAreas();
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
        obtenerUsuarios: function(){
            let frm = new FormData();
            frm.append('request_type','obtenerUsuarios');
            axios.post('/local/ranking/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    let users = [];
                    Object.keys(data).forEach(key => {
                        let dataVal = data[key];
                        let name = dataVal.name;
                        let punto = dataVal.punto;
                        let level = dataVal.level;
                        let img = dataVal.img;

                        let newElem = {
                            'name': name,
                            'punto': punto,
                            'level': level,
                            'img': img
                        };
                        users.push(newElem);
                    });
                    this.users = users;
                });
        },
        obtenerAreas: function(){
            let frm = new FormData();
            frm.append('request_type','obtenerAreas');
            axios.post('/local/ranking/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    let areas = [];
                    Object.keys(data).forEach(key => {
                        let dataVal = data[key];
                        let name = dataVal.name;
                        let punto = dataVal.punto;

                        let newElem = {
                            'name': name,
                            'punto': punto,
                        };
                        areas.push(newElem);
                    });
                    this.areas = areas;
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
            let containerWidth = (document.querySelector("#leves").offsetWidth)/this.prevBanerCount;
            let pages = 2500/containerWidth;
            let marginLeft = 0;
            this.marginLeftBaner += 100;
            if(marginLeft < this.marginLeftBaner) {
                this.marginLeftBaner = (100 * pages - 100)*-1;
            }
            if(this.banerPoint == 1) {
                this.banerPoint = this.pages;
            } else {
                this.banerPoint -= 1;
            }

            console.log(containerWidth);
            console.log(pages);
            console.log(marginLeft);
            console.log(this.marginLeftBaner);

            $('#leves').animate({'margin-left': this.marginLeftBaner+"%"}, 500);
            this.prevBanerCount++;
        },
        nextBaner: function() {
            let containerWidth = (document.querySelector("#levelsContainer").offsetWidth);
            let pages = Math.ceil(2500/containerWidth);
            let marginLeft = (100 * pages - 100)*-1;
            this.marginLeftBaner -= 100;

            console.log(containerWidth);
            console.log(pages);
            console.log(marginLeft);
            console.log(this.marginLeftBaner);

            if(marginLeft >= this.marginLeftBaner) {
                this.marginLeftBaner = 0;
            }
            if(this.banerPoint <  pages) {
                this.banerPoint += 1;
            } else if(this.banerPoint ==  pages) {
                this.banerPoint = 1;
            }
            $('#leves').animate({'margin-left': this.marginLeftBaner+"%"}, 500);
            this.nextBanerCount++;
        },
    }
});