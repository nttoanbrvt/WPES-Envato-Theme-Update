# WPES-Envato-Theme-Update
 Make your Buyers happy with Automatic update your theme through Envato APIs class, made for Theme Authors on Envato Marketplace.
 
## Installing:
 
* Step 1: Moves ```wpes-envato-theme-update.php``` to your theme inc directory.
* Step 2: Put this at the end of your ```functions.php``` file 
```
if( ! function_exists( 'update_my_theme' ) ){
	function update_my_theme() {
		if( class_exists( 'WPES_Envato_Theme_Update' ) ){
			new WPES_Envato_Theme_Update( basename( get_template_directory() ) , 'Purchase code' , 'Buyer personal access token' , false );
		}
	}
	add_action( 'init' , 'update_my_theme' );
}
```
So the code will be looks like this.

```
require get_template_directory() . '/inc/wpes-envato-theme-update.php';
if( ! function_exists( 'update_my_theme' ) ){
	function update_my_theme() {
		if( class_exists( 'WPES_Envato_Theme_Update' ) ){
			new WPES_Envato_Theme_Update( basename( get_template_directory() ) , 'Purchase code' , 'Buyer personal access token' , false );
		}
	}
	add_action( 'init' , 'update_my_theme' );
}
```

As theme author, I believe that you know how to pass the ```purchase code``` and ```buyer personal token key``` values through the Theme Options.

## Demo

!http://puu.sh/lqMd1/23d50a1af9.png!

!http://puu.sh/lqMaN/e0caa535bd.png!


Have a question? feel free to let me know.

[Send me a beer!](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=N4FRYTB3Z5RSL)

Cheers.
