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
            searchCursos: '',
            searchAlumnos: '',
            searchUsers:[],
            backIds: '',
            textMails: '',
            selectedUser: '',
            textMailsSingle: ''
        };
    },
    created(){
        this.getCourses();
        this.sizeWeb();
        window.onresize = this.sizeWeb;
    },
    mounted(){

    },
    computed: {
        searchCourse: function (){
            return this.cursosList.filter((item) => item.name.includes(this.searchCursos));
        },
        // searchUsers: function(){
        //   return this.usuarios.filter((item) => item.name.includes(this.searchAlumnos));
        // },
    },
    methods: {
        getCourses: function() {
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
                        let progress = dataVal.progress;
                        let userIdsMail = dataVal.userIdsMail;

                        let newElem = {
                            'id': id,
                            'name': name,
                            'numEstu': numEstu,
                            'date': date,
                            'progress': progress,
                            'userIdsMail': userIdsMail
                        };
                        courses.push(newElem);
                    });
                    this.cursosList = courses;
                });
        },
        searchName: function(){
            if(this.searchAlumnos != ''){
                this.searchUsers = this.usuarios.filter((item) => item.name.includes(this.searchAlumnos));
            } else{
                this.searchUsers = this.usuarios;
            }
            $('.circlechart').circlechart();
        },
        filterGerencia: function(name){
            this.searchUsers = this.usuarios.filter((item) => item.gerencia.includes(name));
            $('.circlechart').circlechart();
        },
        filterArea: function(name){
            this.searchUsers = this.usuarios.filter((item) => item.area.includes(name));
            $('.circlechart').circlechart();
        },
        filterZona: function(name){
            this.searchUsers = this.usuarios.filter((item) => item.zona.includes(name));
            $('.circlechart').circlechart();
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
                        let id = dataVal.id;
                        let name = dataVal.name;
                        let gerencia = dataVal.gerencia;
                        let area = dataVal.area;
                        let zona = dataVal.zona;
                        let progress = dataVal.progress;

                        let newElem = {
                            'id': id,
                            'name': name,
                            'gerencia': gerencia,
                            'area': area,
                            'zona': zona,
                            'progress': progress
                        };
                        usuarios.push(newElem);
                    });
                    this.usuarios = usuarios;
                    this.searchUsers = this.usuarios;
                    this.gerenciasList = gerenciasList;
                    this.areasList = areasList;
                    this.zonasList = zonasList;
                });
        },
        enviarCorreos: function() {
            let frm = new FormData();
            frm.append('idUsersAll', this.backIds);
            frm.append('message', this.textMails);
            axios.post('../my/email.php', frm)
                .then((response) => {
                    alert('Mensaje enviado');
                });
        },
        enviarCorreosSingle: function() {
            let frm = new FormData();
            frm.append('idUser', this.selectedUser);
            frm.append('message', this.textMailsSingle);
            axios.post('../my/email.php', frm)
                .then((response) => {
                    alert('Mensaje enviado');
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
        },
        closeModal: function(){
            document.querySelector(".back").style.display = "none";
        },
        showModal: function(userIdsMail){
            document.querySelector(".back").style.display = "flex";
            this.backIds = userIdsMail;
        },
        selectUserClick: function(id) {
            this.selectedUser = id;
        },
        showModal2: function(){
            document.querySelector(".back-single").style.display = "flex";
        }
    }
});

$('.circlechart').circlechart();