# imageresize
A simple, yet efficient solution for image resizing and caching with php

## How does it work
imageresize uses a simple technique. Within your images folder you catch all 404 errors with a simple .htaccess file and redirect them to imageresize.php. You configure templates (e.g. thumbnail/big/small) and each template will represent a folder within the images folder. If a image in those folders can't be found the imageresize.php script will generate it for you. 

### Example
You have a /images/pictures/beach.jpg image and you have setup a 'thumbnail' template then you refer to /images/thumbnail/pictures/beach.jpg. The first time you try to open that image it won't exist and imageresize.php will create it for you bases on the template and redirect you to that image again. The next time (next pageview) you open the image it will exists and your webserver will serve it like any other normal file on your filesystem. Because the image file you refer to will actualy exists on the filesystem it will provide the best performance. 

## Drawbacks
There is however one disadvantage: if the original image is edited or removed the resized file will still remain the same since refering to it doesn't trigger the imageresize.php script. You will have to manually delete it or call imageresize.php?clear=thumbnail directly to delete the entire cache for that template
