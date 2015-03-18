<?php get_header(); ?>
<h1>Sign up</h1>


<?php
  if(defined('REGISTRATION_ERROR'))
    foreach(unserialize(REGISTRATION_ERROR) as $error)
      echo "<div class=\"error\">{$error}</div>";
  // errors here, if any

  elseif(defined('REGISTERED_A_USER'))
    echo 'a email has been sent to '.REGISTERED_A_USER;
?>

<form method="post" action="<?php echo add_query_arg('do', 'register', home_url('/')); ?>">
  <label>
    User:
    <input type="text" name="user" value=""/>
  </label>

  <label>
    Email:
   <input type="text" name="email" value="" />
  </label>

  <label>
    Delete this text:
   <input type="text" name="spam" value="some_crappy_spam_protection" />
  </label>

  <input type="submit" value="register" />
</form>

<?php get_footer(); ?>