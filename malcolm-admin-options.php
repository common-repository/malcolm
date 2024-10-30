<div class="wrap">
  <h1>Malcolm!</h1>

  <form action="options.php" method="post">
    <?php
    settings_fields('malcolm');
    do_settings_sections('malcolm');
    submit_button();
    ?>
  </form>
</div>
