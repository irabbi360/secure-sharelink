<div class="wrap">
  <h1>ShareLink Manager</h1>
  <a href="<?php echo admin_url('admin.php?page=sharelink-create'); ?>" class="button button-primary">+ Create New Link</a>
  <table class="widefat mt-3">
    <thead><tr>
      <th>ID</th><th>Resource</th><th>Link</th><th>Uses</th><th>Expires</th><th>Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach($links as $link): ?>
        <tr>
          <td><?php echo $link->id; ?></td>
          <td><?php echo esc_html($link->resource_type); ?></td>
          <td><a href="<?php echo site_url('shareurl?sharelink='.$link->token); ?>" target="_blank">Open</a></td>
          <td><?php echo "{$link->used_count}/" . ($link->max_uses ?: '∞'); ?></td>
          <td><?php echo $link->expires_at ?: 'Never'; ?></td>
          <td>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
              <?php wp_nonce_field('sharelink_delete'); ?>
              <input type="hidden" name="action" value="sharelink_delete">
              <input type="hidden" name="id" value="<?php echo $link->id; ?>">
              <button class="button-link-delete">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
