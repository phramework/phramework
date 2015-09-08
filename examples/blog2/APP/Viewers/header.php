<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <meta name="description" content="">
  <meta name="author" content="">

  <title>Blog Example
    <?php echo $VIEWER_title; ?>
  </title>

  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

  <style>
    html,
    body {
      overflow-x: hidden;
    }

    body {
      padding-top: 70px;
    }

    footer {
      padding: 30px 0;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-fixed-top navbar-inverse">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="?controller=blog">examples/blog</a>
      </div>
      <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          <li <?php if($VIEWER_page == 'blog' ){ echo 'class="active"'; }?>><a href="?controller=blog">Home</a></li>
          <li <?php if($VIEWER_page == 'editor' ){ echo 'class="active"'; }?>><a href="?controller=editor">Editor</a></li>
        </ul>
      </div>
      <!-- /.nav-collapse -->
    </div>
    <!-- /.container -->
  </nav>
  <!-- /.navbar -->

  <div class="container">
