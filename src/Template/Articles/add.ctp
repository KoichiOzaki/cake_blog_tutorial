<h1>Add Article</h1>
<?php
    echo $this->Form->create($article);
    echo $this->Form->control('category_id');
    echo $this->Form->control('title', ['label' => 'title']);
    echo $this->Form->textarea('body', ['rows' => '3', 'label' => 'body']);
    echo $this->Form->button(__('Save Article'));
    echo $this->Form->end();
?>
