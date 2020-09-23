<div class="game-tasa" id="app-mapa">
<div class="contanier-game-load">
    <video id="video" src="tasa.mp4" controls="true"></video>
</div>
<div class="container-game">
<div class="header">
    <div class="millas">{{millas}} MILLAS NAUTICAS</div>
    <div class="right">
    <div class="title">AVANZA Y GANA MILLAS NAUTICAS.</div>
    <div class="subtitle">Las millas nauticas son puntos en el aula virtual.</div>
    </div>
</div>
<div class="pergamino">
    <div class="mapa">
    <div class="boya" v-for="item in cursos" :class="{'active': item.status,'actual': item.actual}">
        <div class="actual" v-if="item.active"></div>
        <div class="img"></div>
        <div class="point">{{item.title}}</div>
    </div>
    <div class="boatch"></div>
    </div>
</div>
</div>
<div class="back-game">
<div class="person-tasa"></div>
<div class="content">
    <div class="title">GANASTE</div>
    <div class="millas">+{{millasAct}} MILLAS NAUTICAS</div>
    <div class="btn">
    <button v-on:click="fade_back()">continuar</button>
    </div>
</div>
</div>
<div class="final">
<div class="luz"></div>
<div class="cofre">
    <div class="tapa"></div>
    <div class="caja"></div>
</div>
<div class="text">ganaste 350 millas nauticas</div>
</div>
</div>