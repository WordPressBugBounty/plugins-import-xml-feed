<div class="wrap">
    <h1 class="wp-heading-inline">Templates</h1>

    <hr />
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <th scope="col" id="title" class="column-title column-primary" style="width: 70%;">
                    <span>Title</span>
                </th>
                <th scope="col" id="column-meta" class="column-column-meta" style="width: 30%;">Date</th>
            </tr>
        </thead>

        <tbody id="the-list" class="moove_importer_templates">
            <?php if ( $data && is_array( $data ) ) : ?>
                <?php foreach ( $data as $template ) : ?>
                    <tr id="post-<?php echo $template['post_id']; ?>" class="iedit author-self level-0 post-<?php echo $template['post_id']; ?> type-tapp_gallery status-publish">
                        <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                            <strong>
                                <a class="row-title" href="<?php echo admin_url( '/options-general.php?page=moove-importer&tab=template_view&template='.$template['post_id']); ?>" aria-label="“<?php echo $template['slug']; ?>” (Edit)">
                                    <?php echo $template['slug']; ?>
                                </a>
                            </strong>
                            <div class="row-actions" style="width: 100%;">
                                <span class="edit"><a href="<?php echo admin_url( '/options-general.php?page=moove-importer&tab=template_view&template='.$template['post_id'] ); ?>" aria-label="Edit ”<?php echo $template['slug']; ?>”">Edit</a> | </span><span class="trash"><a href="" class="submitdelete" aria-label="Delete ”<?php echo $template['slug']; ?>”" data-templateid="<?php echo $template['post_id']; ?>">Delete</a></span>
                            </div>

                        </td>
                        <td class="column-date">
                            <strong><span><?php echo $template['date']; ?></span></strong>
                        </td>
                    </tr>
                <?php endforeach;?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No templates were found!</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" id="title" class="column-title column-primary" style="width: 70%;">
                    <span>Title</span>
                </th>
                <th scope="col" id="column-meta" class="column-column-meta" style="width: 30%;">Date</th>
            </tr>
        </tfoot>
    </table>
</div>
<!--  .wrap -->