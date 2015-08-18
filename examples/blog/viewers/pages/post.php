<div class="row row-offcanvas row-offcanvas-right">

    <div class="col-xs-12 col-sm-9">
        <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
        </p>
        <div class="row">
            <div class="col-xs-6">
                <h2><?php echo $post['title'];?></h2>
                <p><?php echo $post['content'];?></p>
                <!-- <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p> -->
            </div>
            <div class="col-xs-6">
                <h2>Raw data</h2>
                <pre><?php print_r($post);?></pre>
            </div>
        </div><!--/row-->
    </div><!--/.col-xs-12.col-sm-9-->

    <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
        <div class="list-group">
            <a href="?controller=blog" class="list-group-item">Home</a>
            <a href="?controller=editor" class="list-group-item">Editor</a>
        </div>
    </div><!--/.sidebar-offcanvas-->
</div><!--/row-->
