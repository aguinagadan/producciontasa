var explorer = new Vue({
    el: '#explorer',
    delimiters: ['{(', ')}'],
    data(){
        return{
            list: [],
            order: true,
            general: true,
            gerencia: false,
            areas: false,
            zonas: false,
            listPorcent: {},
            totalUsers: 0,
            completedUsers: 0,
            currentCourseId: 0,
            isCalculatedCompleted: false,
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
        getCourseTotalUsers: function(courseId) {
            let frm = new FormData();
            frm.append('courseId',courseId);
            frm.append('request_type','getCursoTotals');
            axios.post('../local/dashboard/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data;
                    this.totalUsers = data.total;
                    this.completedUsers = data.completed;
                });
        },
        getCourseList: function() {
            let frm = new FormData();
            frm.append('request_type','obtenerUserCursos');
            axios.post('../local/dashboard/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    let courses = Array();

                    Object.keys(data).forEach(key => {
                        let dataVal = data[key];
                        let id = dataVal.id;
                        let name = dataVal.name;

                        let newElem = {
                            'id': id,
                            'name': name
                        };
                        courses.push(newElem);
                    });
                    this.list = courses;
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
            this.list = this.list.slice().reverse();
        },
        viewGerencia: function(item){
            if(!this.isCalculatedCompleted) {
                this.getCourseTotalUsers(item.id);
                this.isCalculatedCompleted = true;
            }
            this.currentCourseId = item.id;
            this.general = false;
            this.gerencia = true;
            this.areas = false;
            this.zonas = false;
            let frm = new FormData();
            frm.append('courseId',item.id);
            frm.append('request_type','getGerencias');
            axios.post('../local/dashboard/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    let listPrueba = [];

                    if(data == null) {
                        listPrueba.push({
                            'name': 'No results',
                            'porcent': 0
                        });
                    } else {
                        Object.keys(data).forEach(key => {
                            if (data[key]) {
                                let dataVal = data[key];
                                let name = dataVal.name;
                                let porcent = dataVal.porcent;

                                let newElem = {
                                    'name': name,
                                    'porcent': porcent
                                };
                                listPrueba.push(newElem);
                            }
                        });
                    }
                    this.listPorcent = {
                        item: item,
                        list: listPrueba
                    };
                });
        },
        viewAreas: function(item){
            if(!this.isCalculatedCompleted) {
                this.getCourseTotalUsers(item.id);
                this.isCalculatedCompleted = true;
            }
            this.currentCourseId = item.id;
            this.general = false;
            this.gerencia = false;
            this.areas = true;
            this.zonas = false;
            let frm = new FormData();
            frm.append('courseId',item.id);
            frm.append('request_type','getAreas');
            axios.post('../local/dashboard/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    let listPrueba = [];
                    if(data == null) {
                        listPrueba.push({
                            'name': 'No results',
                            'porcent': 0
                        });
                    } else {
                        Object.keys(data).forEach(key => {
                            if (data[key]) {
                                let dataVal = data[key];
                                let name = dataVal.name;
                                let porcent = dataVal.porcent;

                                let newElem = {
                                    'name': name,
                                    'porcent': porcent
                                };
                                listPrueba.push(newElem);
                            }
                        });
                    }
                    this.listPorcent = {
                        item: item,
                        list: listPrueba
                    };
                });
        },
        viewZonas: function(item){
            if(!this.isCalculatedCompleted) {
                this.getCourseTotalUsers(item.id);
                this.isCalculatedCompleted = true;
            }
            this.currentCourseId = item.id;
            this.general = false;
            this.gerencia = false;
            this.areas = false;
            this.zonas = true;
            let frm = new FormData();
            frm.append('courseId',item.id);
            frm.append('request_type','getZonas');
            axios.post('../local/dashboard/ajax_controller.php',frm)
                .then((response) => {
                    let data = response.data.data;
                    let listPrueba = [];
                    if(data == null) {
                        listPrueba.push({
                            'name': 'No results',
                            'porcent': 0
                        });
                    } else {
                        Object.keys(data).forEach(key => {
                            if (data[key]) {
                                let dataVal = data[key];
                                let name = dataVal.name;
                                let porcent = dataVal.porcent;

                                let newElem = {
                                    'name': name,
                                    'porcent': porcent
                                };
                                listPrueba.push(newElem);
                            }
                        });
                    }
                    this.listPorcent = {
                        item: item,
                        list: listPrueba
                    };
                });
        },
        close: function(){
            this.general = true;
            this.gerencia = false;
            this.areas = false;
            this.zonas = false;
            this.isCalculatedCompleted = false;
        },
        activeOptions: function(key){
            if(!document.querySelector('#option_'+key).classList.contains('active')){
                document.querySelector('#option_'+key).classList.add('active');
            } else{
                document.querySelector('#option_'+key).classList.remove('active');
            }
        }
    }
});