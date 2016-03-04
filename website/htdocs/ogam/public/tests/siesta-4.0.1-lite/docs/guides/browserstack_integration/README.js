Ext.data.JsonP.browserstack_integration({"guide":"<h2 id='browserstack_integration-section-intro'>Intro</h2>\n<div class='toc'>\n<p><strong>Contents</strong></p>\n<ol>\n<li><a href='#!/guide/browserstack_integration-section-intro'>Intro</a></li>\n<li><a href='#!/guide/browserstack_integration-section-authentication'>Authentication</a></li>\n<li><a href='#!/guide/browserstack_integration-section-quick-testing'>Quick testing</a></li>\n<li><a href='#!/guide/browserstack_integration-section-parallelization'>Parallelization</a></li>\n<li><a href='#!/guide/browserstack_integration-section-under-the-hood'>Under the hood</a></li>\n<li><a href='#!/guide/browserstack_integration-section-conclusion'>Conclusion</a></li>\n<li><a href='#!/guide/browserstack_integration-section-see-also%3A'>See also:</a></li>\n<li><a href='#!/guide/browserstack_integration-section-buy-this-product'>Buy this product</a></li>\n<li><a href='#!/guide/browserstack_integration-section-support'>Support</a></li>\n<li><a href='#!/guide/browserstack_integration-section-see-also'>See also</a></li>\n<li><a href='#!/guide/browserstack_integration-section-copyright-and-license'>COPYRIGHT AND LICENSE</a></li>\n</ol>\n</div>\n\n<p>The more tests we write, the more free time we have to improve the quality of our software. Without tests, it's easy to end up constantly chasing the\nsame bugs again and again after each refactoring. A logical step to improving the quality of a web based application is to make\nsure it works in all the various browsers out there. Normally you need to support old and sometimes obsolete operation systems, like\nWindows XP and browsers which require their own VM (IE7, 8, 9 etc). The number of platforms that we want to run our tests on is constantly growing.</p>\n\n<p>One way to solve this requirement is to maintain your own farm of virtual machines with various OS/browser combinations.\nThis can be tricky and will consume lots of your time and resources.\nAnother more elegant way is to use services providing the same infrastructure in the cloud. Thanks to services such as <a href=\"http://www.browserstack.com/\">BrowserStack</a> it is now very simple.</p>\n\n<p>This guide describes the integration facilities that Siesta provides to access the BrowserStack cloud testing infrastructure.</p>\n\n<h2 id='browserstack_integration-section-authentication'>Authentication</h2>\n\n<p>When registering in BrowserStack, you will receive a user name and an access key. You can find these in your BrowserStack account\nunder \"Account -> Automate\" section. Later in this guide we will refer to these as \"BrowserStack username\" and \"BrowserStack access key\"</p>\n\n<h2 id='browserstack_integration-section-quick-testing'>Quick testing</h2>\n\n<p>Assuming your local web server is configured to listen at host \"localhost\" on port 80, all you need to launch your test suite in the cloud\nis to sign up for the BrowserStack trial and run the following command:</p>\n\n<pre><code>__SIESTA_DIR__/bin/webdriver http://localhost/myproject/tests/harness.html --browserstack BS_USERNAME,BS_KEY \n--cap browser=firefox --cap os=windows --cap os_version=XP\n</code></pre>\n\n<p>That's all, the only difference from a normal Siesta automated launch is the \"--browserstack\" option, which is a shortcut performing\na few additional actions. We'll examine what happens under the hood later in this guide.</p>\n\n<p>Note how we have specified the desired OS/browser combination using the \"--cap\" switch (it specifies remote webdriver capability).\nFor a full list of supported capabilities please refer to <a href=\"http://www.browserstack.com/automate/capabilities\">http://www.browserstack.com/automate/capabilities</a></p>\n\n<p>If your webserver listens on a different host (<code>mylocalhost</code> for example) or port (8888), then the \"--browserstack\" option should look like:</p>\n\n<pre><code>--browserstack BS_USERNAME,BS_KEY,mylocalhost,8888\n</code></pre>\n\n<h2 id='browserstack_integration-section-parallelization'>Parallelization</h2>\n\n<p>When using cloud-based infrastructure, each test page is running inside of the own VM, which guarantees the exclusive focus owning\nand allows us to run several test pages in parallel. Naturally, that speed ups the test execution, by the number of parallel sessions\nwe can run.</p>\n\n<p>This can be done using the <code>--max-workers</code> option, that specifies the maximum number of parallel sessions.</p>\n\n<p><strong>Important</strong>. When value of this option is more than 1, the order of tests execution is not defined. A test, that goes lower\nin the <code>harness.start()</code> list, can be executed before the test above it. This is simply because all tests are divided in several\n\"threads\" and all threads are executed simultaneously. You should not rely on some test being run after another, instead,\nevery test should execute standalone (allocate exclusive resources, perform all necessary setup).</p>\n\n<h2 id='browserstack_integration-section-under-the-hood'>Under the hood</h2>\n\n<p>Let's examine what happens under the hood when we use the  \"--browserstack\" shortcut option. In fact, we don't have to use this shortcut\noption and can perform all the steps listed below manually.</p>\n\n<p>1) The first thing that happens is that Siesta establishes a local tunnel from your machine to the BrowserStack server, using the BrowserStack binaries.\nYou can do this step manually by using the batch file in the Siesta package:</p>\n\n<pre><code> __SIESTA_DIR__/bin/browserstacklocal BS_KEY mylocalhost,myportnumber\n</code></pre>\n\n<p>When launched successfully, you should see the following text:</p>\n\n<pre><code>Verifying parameters\n\nStarting local testing\nYou can now access your local server(s) in our remote browser:\nhttp://local:80\n\nPress Ctrl-C to exit\n</code></pre>\n\n<p>2) The \"--host\" option is set to point to the BrowserStack server, based on your username and access key:</p>\n\n<pre><code>--host=\"http://BS_USERNAME:BS_KEY@hub.browserstack.com/wd/hub\"\n</code></pre>\n\n<p>3) The browserstack specific capability \"browserstack.local\" is set to \"true\"</p>\n\n<p>To sum up, instead of using the \"--browserstack\" shortcut option, we could:</p>\n\n<ul>\n<li><p>launch the tunnel manually:</p>\n\n<pre><code>  __SIESTA_DIR__/bin/browserstacklocal BS_KEY,mylocalhost,myportnumber\n</code></pre></li>\n<li><p>specify the command as:</p>\n\n<pre><code>  __SIESTA_DIR__/bin/webdriver http://localhost/myproject/tests/harness.html \n      --host=\"http://BS_USERNAME:BS_KEY@hub.browserstack.com/wd/hub\" \n      --cap browser=firefox --cap os=windows --cap os_version=XP \n      --cap browserstack.local=true\n</code></pre></li>\n</ul>\n\n\n<p>For convenience, instead of setting the \"--host\" option manually, one can specify \"--browserstack-user\" and \"--browserstack-key\" options.</p>\n\n<pre><code>    __SIESTA_DIR__/bin/webdriver http://localhost/myproject/tests/harness.html \n        --browserstack-user=BS_USERNAME --browserstack-key=BS_KEY\n        --cap browser=firefox --cap os=windows --cap os_version=XP \n        --cap browserstack.local=true\n</code></pre>\n\n<h2 id='browserstack_integration-section-conclusion'>Conclusion</h2>\n\n<p>As you can see, thanks to the excellent <a href=\"http://www.browserstack.com\">BrowserStack</a> infrastructure, launching your tests in the cloud is as easy as specifying\none extra argument on the command line. The benefits of cloud testing are obvious - no need to waste time and resources setting up and maintaining your own VM farm,\nand additionally you can run your test suite in various browsers in parallel.</p>\n\n<h2 id='browserstack_integration-section-see-also%3A'>See also:</h2>\n\n<p><a href=\"http://www.browserstack.com/local-testing\">http://www.browserstack.com/local-testing</a></p>\n\n<h2 id='browserstack_integration-section-buy-this-product'>Buy this product</h2>\n\n<p>Visit our store: <a href=\"http://bryntum.com/store/siesta\">http://bryntum.com/store/siesta</a></p>\n\n<h2 id='browserstack_integration-section-support'>Support</h2>\n\n<p>Ask a question in our community forum: <a href=\"http://www.bryntum.com/forum/viewforum.php?f=20\">http://www.bryntum.com/forum/viewforum.php?f=20</a></p>\n\n<p>Share your experience in our IRC channel: <a href=\"http://webchat.freenode.net/?randomnick=1&amp;channels=bryntum&amp;prompt=1\">#bryntum</a></p>\n\n<p>Please report any bugs through the web interface at <a href=\"https://www.assembla.com/spaces/bryntum/support/tickets\">https://www.assembla.com/spaces/bryntum/support/tickets</a></p>\n\n<h2 id='browserstack_integration-section-see-also'>See also</h2>\n\n<p>Web page of this product: <a href=\"http://bryntum.com/products/siesta\">http://bryntum.com/products/siesta</a></p>\n\n<p>Other Bryntum products: <a href=\"http://bryntum.com/products\">http://bryntum.com/products</a></p>\n\n<h2 id='browserstack_integration-section-copyright-and-license'>COPYRIGHT AND LICENSE</h2>\n\n<p>Copyright (c) 2009-2015, Bryntum AB &amp; Nickolay Platonov</p>\n\n<p>All rights reserved.</p>\n","title":"Cloud testing. BrowserStack integration"});