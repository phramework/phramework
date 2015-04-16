<form class="form" method="post" action="<?php echo '?controller=blog'; ?>">
    <fieldset>
        <legend>Create new post</legend>
        <div class="form-group">
            <label for="title">Post title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Title...">
        </div>
        <div class="form-group">
            <label for="content">Post content</label>
            <textarea class="form-control" id="content" name="content" placeholder="Content..." rows="10"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Submit</button>
    </fieldset>
</form>