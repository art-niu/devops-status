<?php

echo '
<form action="return.php" class="js-ajax-php-json" method="post" accept-charset="utf-8">
  <input type="text" name="favorite_beverage" value="" placeholder="Favorite restaurant" />
  <input type="text" name="favorite_restaurant" value="" placeholder="Favorite beverage" />
  <select name="gender">
    <option value="male">Male</option>
    <option value="female">Female</option>
  </select>
  <input type="submit" name="submit" value="Submit form"  />
</form>';
		
?>
