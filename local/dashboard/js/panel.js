var explorer = new Vue({
    el: '#explorer',
    delimiters: ['{(', ')}'],
    data(){
        return{
            cursosList: [
                {
                    id: '1',
                    name: 'Ergonomia 2020',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
                {
                    id: '1',
                    name: 'IPERC',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
                {
                    id: '1',
                    name: 'Riesgo en instalaciones 2020',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
                {
                    id: '1',
                    name: 'Material desing',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
                {
                    id: '1',
                    name: 'Inducción SSOMA',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
                {
                    id: '1',
                    name: 'Habilidades bladas',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
                {
                    id: '1',
                    name: 'Operacciones pesqueras',
                    numEstu: 751,
                    date: "6 de junio de 2020",
                    progress: 90
                },
            ],
            usuarios: [
                {
                    name: 'Juan Matias Rodriguez',
                    gerencia: "Gerencia GH",
                    area: "logistica",
                    zona: "Corporativo",
                    progress: 0
                },
                {
                    name: 'Juan Matias Rodriguez',
                    gerencia: "Gerencia GH",
                    area: "logistica",
                    zona: "Corporativo",
                    progress: 0
                },
                {
                    name: 'Juan Matias Rodriguez',
                    gerencia: "Gerencia GH",
                    area: "logistica",
                    zona: "Corporativo",
                    progress: 0
                },
            ],
            gereniasList: [
                {name:"Gerencia de mantenimiento"},
                {name:"Gerencia de operaciones"},
                {name:"Gerencia GH"},
                {name:"Gerencia finanzas"},
                {name:"Gerencia general"},
                {name:"Gerencia TI"},
            ],
            areasList: [
                {name:"Contabilidad"},
                {name:"Operaciones"},
                {name:"Auditoria"},
                {name:"Administracion y finanzas"},
                {name:"TI"},
                {name:"Mantenimiento"},
            ],
            zonasList:[
                {name:"Zona Este"},
                {name:"Zona Este"},
                {name:"Zona Este"},
            ],
            act: {},
            order: true,
            users: false,
            general: true,
            listPorcent: {},
        }
    },
    created(){
        this.sizeWeb();
        window.onresize = this.sizeWeb;

    },
    mounted(){

    },
    methods: {
        sizeWeb: function(){
            if (window.innerWidth < 768)
                this.menu = false;
            else
                this.menu = true;
        },
        changeOrder: function(){
            this.order = this.order ? false : true;
            //   aqui adjuntar el codigo de ordenamiendo desde la api
        },
        viewUser: function(){
            this.general = false;
            this.users = true;
            this.act = {
                name: "Ergonomia 2020"
            }
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