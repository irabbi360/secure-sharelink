<div class="wrap">
  <h1>Access Logs</h1>
  <table class="widefat mt-3">
    <thead>
      <tr>
        <th>ID</th>
        <th>Link ID</th>
        <th>IP</th>
        <th>Status</th>
        <th>User Agent</th>
        <th>Accessed</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($logs as $log): ?>
        <tr>
          <td><?php echo $log->id; ?></td>
          <td><?php echo $log->link_id; ?></td>
          <td><?php echo $log->ip_address; ?></td>
          <td><?php echo $log->status; ?></td>
          <td><?php echo $log->user_agent; ?></td>
          <td><?php echo $log->accessed_at; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
