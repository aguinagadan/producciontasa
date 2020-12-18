var explorer = new Vue({
    el: '#explorer',
    delimiters: ['{(', ')}'],
    data(){
        return{
            cursosList: [],
            usuarios: [],
            gerenciasList: [],
            areasList: [],
            zonasList:[],
            act: {},
            order: true,
            orderUser: true,
            users: false,
            general: true,
            listPorcent: {},
        };
    },
    created(){
        this.sizeWeb();
        window.onresize = this.sizeWeb;

    },
    mounted(){
        this.getCourseList();
    },
    methods: {
        getCourseList: function() {
            let frm = new FormData();
            frm.append('request_type','panelUserCursos');
            axios.post('../local/dashboard/ajax_controller.php', frm)
                .then((response) => {
                    let data = response.data.data;
                    let courses = Array();

                    Object.keys(data).forEach(key => {
                        let dataVal = data[key];
                        let id = dataVal.id;
                        let name = dataVal.name;
                        let numEstu = dataVal.numEstu;
                        let date = dataVal.date;
                        let progress = 25;

                        let newElem = {
                            'id': id,
                            'name': name,
                            'numEstu': numEstu,
                            'date': date,
                            'progress': progress
                        };
                        courses.push(newElem);
                    });
                    this.cursosList = courses;
                });
        },
        sizeWeb: function(){
            if (window.innerWidth < 768)
                this.menu = false;
            else
                this.menu = true;
        },
        changeOrder: function(){
            this.order = this.order ? false : true;
            this.cursosList = this.cursosList.slice().reverse();
        },
        changeOrderUser: function(){
            this.orderUser = this.orderUser ? false : true;
            this.usuarios = this.usuarios.slice().reverse();
        },
        viewUser: function(cursoId){
            this.general = false;
            this.users = true;
            let frm = new FormData();
            frm.append('courseId', cursoId);
            frm.append('request_type','getUsuariosByCurso');
            axios.post('../local/dashboard/ajax_controller.php', frm)
                .then((response) => {
                    let usuarios = Array();

                    this.act = {
                        name: response.data.nombreCurso
                    };

                    let gerenciasList = response.data.gerenciasList;
                    let areasList = response.data.areasList;
                    let zonasList = response.data.zonasList;

                    let data = response.data.data;

                    Object.keys(data).forEach(key => {
                        let dataVal = data[key];
                        let name = dataVal.name;
                        let gerencia = dataVal.gerencia;
                        let area = dataVal.area;
                        let zona = dataVal.zona;
                        let progress = dataVal.progress;

                        let newElem = {
                            'name': name,
                            'gerencia': gerencia,
                            'area': area,
                            'zona': zona,
                            'progress': progress
                        };
                        usuarios.push(newElem);
                    });
                    this.usuarios = usuarios;
                    this.gerenciasList = gerenciasList;
                    this.areasList = areasList;
                    this.zonasList = zonasList;
                });
        },
        close: function(){
            this.general = true;
            this.users = false;
        },
        activeOptions: function(key){
            if(!document.querySelector('#option_'+key).classList.contains('active')){
                document.querySelector('#option_'+key).classList.add('active');
            } else{
                document.querySelector('#option_'+key).classList.remove('active');
            }
        },
        activeSubmenu: function(elem){
            if(!document.querySelector('#'+elem).classList.contains('show')){
                document.querySelector('#'+elem).classList.add('show');
            } else{
                document.querySelector('#'+elem).classList.remove('show');
            }
        }
    }
});

$('.circlechart').circlechart();