# WPES-Envato-Theme-Update
 Automatic update your theme through Envato APIs, made for Theme Authors on Envato Marketplace.
 
## Installing:
 
* Step 1: Moves ```wpes-envato-theme-update.php``` to your theme inc directory.
* Step 2: Put this at the end of your ```functions.php``` file ```new WPES_Envato_Theme_Update( basename( get_template_directory() ) , 'Purchase Code', 'Buyer Personal Access Token' );```

```
require get_template_directory() . '/inc/wpes-envato-theme-update.php';
new WPES_Envato_Theme_Update( basename( get_template_directory() ) , 'Purchase Code', 'Buyer Personal Access Token' );
```
So the code will be looks like this.

As theme author, I believe that you know how to pass the purchase code and buyer personal token key through the class.

Cheers.