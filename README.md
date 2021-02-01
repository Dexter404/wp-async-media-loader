# wp-async-media-loader

WordPress plugin to demonstrate asynchronous loading of featured image to any custom post in WordPress plugin.

## How to install?

Copy the PHP script to WordPress plugin directory (`/wordpress/wp-content/plugins/`) and activate from Plugins page.

## How to use?

Once activated, you can go to Custom Post page `http://localhost:8888/wordpress/wp-admin/edit.php?post_type=async_media_loader`.

Initially custom post view will be empty. 
- To add some, pass `custom_post_load=true` in query params like `http://localhost:8888/wordpress/wp-admin/edit.php?post_type=async_media_loader?custom_post_load=true`. 
- To reset use `custom_post_reset=true` in query params like `http://localhost:8888/wordpress/wp-admin/edit.php?post_type=async_media_loader?custom_post_reset=true`.

For tutorial, visit the post [here](https://rahul-arora.medium.com/asynchronous-media-load-in-wordpress-plugin-4d4cd6734d55).

## Screenshots

![image](https://raw.githubusercontent.com/Dexter404/wp-async-media-loader/main/screenshots/Screenshot-1.png)
![image](https://raw.githubusercontent.com/Dexter404/wp-async-media-loader/main/screenshots/Screenshot-2.png)
![image](https://raw.githubusercontent.com/Dexter404/wp-async-media-loader/main/screenshots/Screenshot-3.png)
