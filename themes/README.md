# Theme's views creation rule

Create theme page by cherry-picking sections from the each available themes HTML theme without changing its original HTML structure or class names more than necessary.  

INPUTS
- Source theme folder: determined theme name existed in public/theme-reference folder
  (contains example pages like index.html, about.html, contact.html, blog.html, etc.)
- Destination Blade view (product page): themes/views folder 
- Assets are already under /public/theme-reference/{determined theme} (keep using originl CSS/JS/images from public), and copy all the asset to /storage/app/public/themes/{determined theme} (just create the placeholder, and ill copy it myself). Rewrite URLs using Laravel’s asset() helper. 

STRICT RULES
1) Change as little as possible of the original theme markup and class names. 
2) Cherry-pick whole sections from the source HTML files. If an exact section doesn’t exist, adapt the closest equivalent with minimal tweaks.
3) Keep responsiveness and original CSS/JS intact.
4) Do not rename core classes or restructure nested elements unless strictly required to integrate with Blade.
5) Convert only what’s needed to Blade (asset() paths, loops/vars). No framework-specific rewrites beyond that.

SPECIFIC RULE FOR EACH PAGE :
---------------
COMPONENTS
A) Nav Menu : Brand/Logo, Nav Link based on page/menu visibility, sticky header, and icons
B) Footer : Brand/Logo, quick links based on page/menu visibility, address
---------------
HOME PAGE :
Components - Nav Menu
A) Hero section with tagline, heading, short description, and a clear call-to-action button.
B) About section (short intro text + image; adapt from any banner/blog/about block).
C) Products grid (5 top is_featured).
D) Services list.
E) Testimonials (contain picture, name, title, and testimoni).
F) Contact form (adapt from contact page).
G) Map placeholder (static container;).
Components - Footer
---------------
PRODCUT PAGE :
Components - Nav Menu
A) title : Produk Kami (editable from admin page)
B) hero section (editable from admin page)
C) functional Search bar 
D) functional filter sectin, harga, kategori, terjual, etc. 
E) list produk complete with button to product details, pagination 15 (showing products)
Components - Footer
