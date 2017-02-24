{include file='head.tpl' htmlTitle="{#interviewList#}"}

<span id="form-data" data-name="interviews" data-page="new"></span>
<script src="{version_hash src="/js/alias.generate.js"}"></script>

<style>
.header-bg {
    /* background-image: url(/media/u17.jpg); */
    /* background-size: cover; */
    /* opacity: 0.8; */
    /* width: 100%; */
    background-image: url(/media/teacher3.jpg);
    background-position-x: -200px;
    background-position-y: -270px;
    color: white;
    height: 250px;
    opacity: 1;
    padding: 10px;
}

.header-bg h1 {
    opacity:1;
font-family: "Whitney SSm A", "Whitney SSm B", Avenir, "Segoe UI", Ubuntu, "Helvetica Neue", Helvetica, Arial, sans-serif ;
font-size: 40px ;
font-stretch: normal ;
font-style: normal ;
font-variant-caps: normal ;
font-variant-ligatures: normal ;
font-variant-numeric: normal ;
font-weight: 500 ;
text-align: center;
margin-top: 80px;
text-shadow: 1px 1px #678DD7;
}

body {
    height: 100%;
    margin: 0;
    background: #fff;
    -webkit-font-smoothing: antialiased;
}
</style>

<div class="page-header header-bg">
  <h1>Learn programming by solving problems.</h1>
  <!--   <p><a href="/course/new" class="btn btn-primary" role="button">Get Started</a> </p> -->
</div>

<div class="row">
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="/media/u30.png" alt="...">
      <div class="caption">
        <h3>Create your course</h3>
        <p>Courses that meet your needs. You can create your own problems.</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="/media/u35.png" alt="...">
      <div class="caption">
        <h3>Manage the progres of your students</h3>
        <p>Courses that meet your needs. You can create your own problems.</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-4">
    <div class="thumbnail">
      <img src="/media/u30.png" alt="...">
      <div class="caption">
        <h3>Create your  problems</h3>
        <p>Courses that meet your needs. You can create your own problems.</p>
      </div>
    </div>
  </div>
</div>


{include file='footer.tpl'}

