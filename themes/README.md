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

PROMO DISPLAY RULES
- Every product list (landing, detail recommendations, cart summary, shipping, payment) must surface an active promotion by showing the promo label badge and rendering the original price with a strike-through above the discounted price.
- When no promotion is active or the promo period has ended, only the normal price should be shown and the promo badge must be hidden.
- Cart, shipping, and payment totals must rely on the discounted price when a promo is active so expired promotions never affect the payable amount.
- Keep markup adjustments minimal: append helper classes (e.g. `.promo-label`, `.price-original`, `.price-current`) instead of rewriting structural containers.
- Theme Restoran implements these helpers via `.promo-label` badges and `.price-stack` containers across its listing, detail, cart, shipping, and payment views—preserve them when extending the theme so promo states stay consistent.

SPECIFIC RULE FOR EACH PAGE :
---------------
COMPONENTS
A) Nav Menu : Brand/Logo, Nav Link based on page/menu visibility, sticky header, and icons
B) Footer : Brand/Logo, quick links based on page/menu visibility, address
C) Flying Whatsapp/Phone Button : fixed button on the bottom corner of all landing pages, can be showed, hided, added, and edited (the link inside button). 
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
----------------
PRODUCT DETAILS PAGE :
Components - Nav Menu
A) Product Name (heading)
B) Product Image
C) slider containing thumbnal of product image (to choice which product image to show in product image section)
D) Prduct Price
E) button to increment and decrement number
F) masukkan ke keranjang button
G) Product's comment (can be toggle active or not in admin page)
H) recomendatin of 5 similiar products
Components - Footer
-----------------
KERANJANG/CART PAGE :
Components - Nav Menu
A) H1 saying keranjang
B) List of items table : single photo - name - price - quantity (can be incremented or decremented)
C) Row of total : change dynamicly according to quantity changed
D) Button continue to shipping (if biteship service exist and shipping turned on) to payment otherwis
Components - Footer
-----------------
ORDER PAGE :
Components - Nav Menu
A) H1 saying pesananan
B) List of order items table  : single photo - name - price - status (if shipping enabled, show the shipping status, if shipping not enabled, show confirmed/ not confirmed)
Components - Footer
-----------------
ABOUT US PAGE
Components - Nav Menu
A) Header saying tentang kami
B) section about brief explanation about us (2 columns, 1 clumn for image, other : title & description)
C) section showing quote
D) section saying tim kami : row contain several image thumbnail and brief text below it, can be cruded from admin page
E) section keunggulan kami, row contain seeral cards with icon, title, and description below it can be cruded from admin page inclusing the icon
Components - Footer
-----------------
ARTIKEL PAGE (This page have to be SEO Friendly)
Components - Nav Menu
A) Header saying artikel
B) list of articles (image thumbnails/placeholder if not set, title, several line of contents, date made, button to read) - Left Column
C) search bar - top right column
D) timeline years -> subbed month -> list of articles made onthat month - right column below search bar
Components - Footer
-----------------
ARTICLE DETAIL PAGE (This page have to be SEO Friendly)
Components - Nav Menu
A) Header saying judul of artikel
B) date of made
C) article content
D) Comment section (can be turned of from kelola halaman)
E) 3 other article recomedation
Components - Footer
-----------------
SHIPPING PAGE
Components - Nav Menu
A) without header
B) forms - for address (address, provinces, kecamatan, kelurahan, name, email, phone, postal code) provinces filter kecamatan, kecamatan filter kelurahan, kelurahan generate postal code
C) below form right after postal code filled, show available shipping method
D) right bar, shows prodcut list price from kerangjang page, and after subtotal, add shipping cost which dynamically get from selected shipping method, and belw that add total
E) payment button under the right sidebar which connect to payment gateway page according to payment gateway selected 
------------------
GALlERY PAGE
Components - Nav Menu
A) Header saying Galeri
B) list of thumbnails of photos in right side, with click to zoom modal
C) category filter of gallery in left side
D) Components - Footer
-------------------
CONTACT PAGE
Components - Nav Menu
A) Header saying Kontak Kami
B) section consisting of cards of contact info, email, alamat, no telp, better if the cards can be managed in admin page
C) links of social media including their socmed icon, each socmed can be hide/show
D) section showing map just like in home page, can be hide and show
Components - Footer
