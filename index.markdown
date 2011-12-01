---
layout: default
---

<div class="holder_content">
    <section class="group5" id="installation">
        <h3>Installation</h3>
        <p>Get the code:</p>
        <p><input type="text" value="git clone git://github.com/willdurand/Geocoder.git" size="74" class="git" /></p>
        <p>If you don't use a <em>ClassLoader</em> in your application, just require the provided autoloader:</p>
{% highlight php %}
<?php

require_once 'path/to/geocoder/src/autoload.php';
{% endhighlight %}
        <p>You're done.</p>
        <p><br /></p>
        <p>Now, you need an <code>HTTP Adapter</code> to query an API. Then, you have to choose a <code>provider</code> which is closed to what you want to get. <strong>Geocoder</strong> provides a lot of providers, you can use one of them or write your own. You can also register all providers and decide later.</p>
    </section>
    <section class="group6">
        <br />
        <br />
{% highlight php %}
<?php

// Create an adapter
$adapter  = new \Geocoder\HttpAdapter\BuzzHttpAdapter();

// Create a Geocoder object and pass it your adapter
$geocoder = new \Geocoder\Geocoder();

// Then, register all providers your want
$geocoder->registerProviders(array(
    new \Geocoder\Provider\YahooProvider(
        $adapter, '<YAHOO_API_KEY>', $locale
    ),
    new \Geocoder\Provider\IpInfoDbProvider(
        $adapter, '<IPINFODB_API_KEY>'
    ),
    new \Geocoder\Provider\HostIpProvider($adapter)
));
{% endhighlight %}
    </section>
</div>
<div class="holder_content">
    <section class="group4" id="api">
        <h3>API</h3>
        <table>
            <tr>
                <th>Geocoding</th>
                <th>Reverse Geocoding</th>
            </tr>
            <tr>
                <td>
                    <span class="address">$geocoder->geocode('88.188.221.14');</span>
                    <br />
                    <span class="annotation">IP address</span>
                </td>
                <td>
                    <span class="address">$geocoder->reverse($latitude, $longitude);</span>
                    <br />
                    <span class="annotation">geographic coordinates</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="address">$geocoder->geocode('10 rue Gambetta, Paris, France');</span>
                    <br />
                    <span class="annotation">street address</span>
                </td>
                <td></td>
            </tr>
        </table>
{% highlight php %}
<?php

$result = $geocoder->geocode('88.188.221.14');
// Result is:
// "latitude"       => string(9) "47.901428"
// "longitude"      => string(8) "1.904960"
// "streetNumber"   => string(0) ""
// "streetName"     => string(0) ""
// "city"           => string(7) "Orleans"
// "zipcode"        => string(0) ""
// "county"         => string(6) "Loiret"
// "region"         => string(6) "Centre"
// "country"        => string(6) "France"

$result = $geocoder->geocode('10 rue Gambetta, Paris, France');
// Result is:
// "latitude"       => string(9) "48.863217"
// "longitude"      => string(8) "2.388821"
// "streetNumber"   => string(2) "10"
// "streetName"     => string(15) "Avenue Gambetta"
// "city"           => string(5) "Paris"
// "county"         => string(5) "Paris"
// "zipcode"        => string(5) "75020"
// "region"         => string(14) "Ile-de-France"
// "country"        => string(6) "France"

$result = $geocoder->reverse($latitude, $longitude);
{% endhighlight %}
        <p><br /></p>
        <p>The <code>$result</code> object is an instance of the <code>Geocoded</code> class which implements <a href="http://php.net/manual/class.arrayaccess.php">ArrayAccess</a> and the following API:</p>
        <ul>
            <li><code>getCoordinates()</code> will return an array with <code>latitude</code> and <code>longitude</code> values;</li>
            <li><code>getLatitude()</code> will return the <code>latitude</code> value;</li>
            <li><code>getLongitude()</code> will return the <code>longitude</code> value;</li>
            <li><code>getStreetNumber()</code> will return the <code>street number/house number</code> value;</li>
            <li><code>getStreetName()</code> will return the <code>street name</code> value;</li>
            <li><code>getCity()</code> will return the <code>city</code> value;</li>
            <li><code>getZipcode()</code> will return the <code>zipcode</code> value;</li>
            <li><code>getCounty()</code> will return the <code>county</code> value;</li>
            <li><code>getRegion()</code> will return the <code>region</code> value;</li>
            <li><code>getCountry()</code> will return te <code>country</code> value.</li>
        </ul>
        <p><br /></p>
        <p>The Geocoder's API is fluent, you can write:</p>
{% highlight php %}
<?php

$result = $geocoder
    ->registerProvider(new \My\Provider\Custom($adapter))
    ->using('custom')
    ->geocode('68.145.37.34')
    ;
{% endhighlight %}
    <p><br /></p>
    <p>The <code>using()</code> method allows you to choose the adapter to use. When you deal with multiple adapters, you may want to choose one of them. The default behavior is to use the first one but it can be annoying.</p>
    </section>
</div>
<div class="holder_content">
    <section class="group4" id="providers">
        <h3>Providers</h3>
        <p><strong>Geocoder</strong> comes with a lot of service providers:</p>
        <table>
            <tr>
                <th>IP-Based</th>
                <th>Address-Based</th>
            </tr>
            <tr>
                <td><a href="http://freegeoip.net/static/index.html">FreeGeoIp</a> as IP-Based geocoding provider</td>
                <td><a href="http://code.google.com/apis/maps/documentation/geocoding/">Google Maps</a> as Address-Based geocoding and reverse geocoding provider</td>
            </tr>
            <tr>
                <td><a href="http://www.hostip.info/">HostIp</a> as IP-Based geocoding provider</td>
                <td><a href="http://msdn.microsoft.com/en-us/library/ff701715.aspx">Bing Maps</a> as Address-Based geocoding and reverse geocoding provider</td>
            </tr>
            <tr>
                <td><a href="http://www.ipinfodb.com/">IpInfoDB</a> as IP-Based geocoding provider</td>
                <td><a href="http://nominatim.openstreetmap.org/">OpenStreetMaps</a> as Address-Based geocoding and reverse geocoding provider</td>
            </tr>
            <tr>
                <td><a href="http://developer.yahoo.com/geo/placefinder/">Yahoo! PlaceFinder</a> as IP-Based geocoding provider</td>
                <td><a href="http://developers.cloudmade.com/projects/show/geocoding-http-api">CloudMade</a> as Address-Based geocoding and reverse geocoding provider</td>
            </tr>
            <tr>
                <td></td>
                <td><a href="http://developer.yahoo.com/geo/placefinder/">Yahoo! PlaceFinder</a> as Address-Based geocoding and reverse geocoding provider</td>
            </tr>

        </table>
    </section>
</div>
<div class="holder_content">
    <section class="group4" id="extending_things">
        <h3>Extending Things</h3>
        <p>You can provide your own <code>adapter</code>, you just need to create a new class which implements <code>HttpAdapterInterface</code>.</p>
        <p>You can also write your own <code>provider</code> by implementing the <code>ProviderInterface</code>.</p>
        <p>Note, the <code>AbstractProvider</code> class can help you by providing useful features.</p>
    </section>
</div>
<div class="holder_content">
    <section class="group4" id="about">
        <h3>About</h3>
        <p>
            <strong>Geocoder</strong> has been created by William Durand and awesome <a href="https://github.com/willdurand/Geocoder/contributors">contributors</a>.
        </p>
        <p><br /></p>
        <p>The MIT License</p>
        <p>Copyright (c) 2010-2011 william.durand1[at]gmail.com</p>
        <p>Permission is hereby granted, free of charge, to any person obtaining a copy
        of this software and associated documentation files (the "Software"), to deal
        in the Software without restriction, including without limitation the rights
        to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
        copies of the Software, and to permit persons to whom the Software is
        furnished to do so, subject to the following conditions:
        </p>
        <p></p>
        <p>The above copyright notice and this permission notice shall be included in
        all copies or substantial portions of the Software.
        </p>
        <p></p>
        <p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
        AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
        OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
        THE SOFTWARE.
        </p>
    </section>
</div>
