<h1 align="center">💳 Puffx Pay - WordPress Plugin</h1>

<p align="center">
  Accept secure online payments on your WordPress website using the <strong>Puffx Pay API</strong>.<br>
  Fully compatible with <strong>WooCommerce</strong> and supports <strong>Webhook integration</strong> for instant payment updates.
</p>

<hr>

<h2>📖 Overview</h2>
<p>
Puffx Pay WordPress plugin allows you to integrate the Puffx Pay payment gateway into your WooCommerce store.
It provides a seamless and secure payment experience for your customers, with real-time status updates using webhooks.
</p>

<h2>✨ Features</h2>
<ul>
  <li>🔌 Easy installation & setup</li>
  <li>🔒 Secure payment processing via Puffx Pay API</li>
  <li>⚡ Optimized for speed and reliability</li>
  <li>📲 Mobile-friendly checkout</li>
  <li>🔔 Instant payment notification via Webhook</li>
  <li>💱 Multi-currency support</li>
</ul>

<h2>📦 Installation</h2>
<ol>
  <li>Download the plugin ZIP file.</li>
  <li>In WordPress dashboard, go to <strong>Plugins → Add New → Upload Plugin</strong>.</li>
  <li>Upload the ZIP file and click <strong>Activate</strong>.</li>
  <li>Go to <strong>WooCommerce → Settings → Payments → Puffx Pay</strong> to configure the plugin.</li>
</ol>

<h2>⚙️ Configuration</h2>
<ul>
  <li><strong>Enable/Disable:</strong> Activate or deactivate Puffx Pay.</li>
  <li><strong>Title:</strong> Display name on checkout page (Default: Puffx Pay).</li>
  <li><strong>Description:</strong> Info displayed during checkout.</li>
  <li><strong>API Key (user_token):</strong> Enter your Puffx Pay API key from dashboard.</li>
  <li><strong>Route:</strong> Select <strong>Route 1 (Normal)</strong> for standard processing.</li>
  <li><strong>Redirect URL:</strong> Page where customer will be sent after payment.</li>
  <li><strong>Webhook URL:</strong> Paste this into your Puffx Pay dashboard for instant updates.</li>
</ul>

<h2>🔗 Example Webhook URL</h2>
<pre><code>https://yourdomain.com/wp-content/plugins/puffx-pay/webhook-handler.php</code></pre>

<h2>💻 Example: webhook-handler.php</h2>
<pre><code>&lt;?php
// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from Puffx Pay
    $status = $_POST['status'];
    $order_id = $_POST['order_id'];
    $customer_mobile = $_POST['customer_mobile'];
    $amount = $_POST['amount'];
    $remark1 = $_POST['remark1'];
    $remark2 = $_POST['remark2'];

    // Process data (e.g., save to DB, update order, log)
    // Your code here...
    
} else {
    http_response_code(405); // Method Not Allowed
    echo 'Only POST requests are allowed';
}
?&gt;
</code></pre>

<h2>💱 Supported Currencies</h2>
<ul>
  <li>INR (₹)</li>
  <li>USD ($) Coming Soon...</li>
  <li>PKR (£) Coming Soon...</li>
  <li>And more…</li>
</ul>

<h2>📋 Requirements</h2>
<ul>
  <li>WordPress 5.0 or higher</li>
  <li>WooCommerce 4.0 or higher</li>
  <li>PHP 7.2+</li>
  <li>SSL certificate (for secure payments)</li>
</ul>

<h2>🛠 Changelog</h2>
<ul>
  <li><strong>v1.0.0</strong> – Initial release with full Puffx Pay integration.</li>
</ul>

<h2>🛟 Support & Contact</h2>
<p>
Need help or want to report an issue?<br>
🌐 Website: <a href="https://puffxhost.com/contact" target="_blank">https://puffxhost.com/contact</a><br>
📧 Email: <a href="mailto:support@puffxhost.in">support@puffxhost.in</a><br>
📞 WhatsApp: <a href="https://wa.me/918602967573" target="_blank">+91 8602967573</a>
</p>

<h2>📜 License</h2>
<p>
This plugin is licensed under the <strong>MIT License</strong> – you are free to use, modify, and distribute it.
</p>

<h2>👨‍💻 Author</h2>
<p>
  Made with ❤️ by <a href="https://github.com/puffxhost" target="_blank">Nitin Mehta</a><br>
  Instagram: <a href="https://instagram.com/unknown_coder1x" target="_blank">@unknown_coder1x</a>
</p>

<hr>
<p align="center">
  Developed with ❤️ by <strong>Nitin Mehta</strong>
</p>
