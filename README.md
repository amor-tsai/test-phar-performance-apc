# Application to test Phar + APC performance

Based on slim framework as the baseline.  THe question being asked is "does simply adding a phar application add any overhead".  But also, "does pulling one small class out of a phar add more overhead than simply having it on the filesystem".

The contained application attempts to answer these questions with 3 scripts:

* ```index.php``` - baseline app, no extra libraries
* ```index-with-filesys.php``` - app with filesystem library added 
* ```index-with-phar.php``` - app with phar based library added

The command I ran to test performance:

    ab -H "Connection: close" -n 500 http://test.dev/index-with-phar.php
    // or
    ab -H "Connection: close" -n 500 http://test.dev/index-with-filesys.php
    
    
After reading this post ( http://www.reddit.com/r/PHP/comments/13uwgk/phar_performance/ ), and having my own curiosity of the current state of things, I wanted to dig in an see what the performance of APC with phar files was.

I've found some oddities in performance, and I am hoping before I do any deeper investigation, someone might know something.

The app is here:
https://github.com/ralphschindler/test-phar-performance-apc/

The library I am attempting to introduce to the app is Zend_Db.phar.  I chose this b/c I fully know the codebase, and the phar clocks in at 519844 bytes uncompressed.

It breaks down like this:

    /index.php - baseline app without addition of library
    /index-with-filesys.php - Zend_Db library extracted to filesystem
    /index-with-phar.php - Zend_Db as phar included

Without APC, on my laptop, I get a slight drop off, but acceptable.  With APC, the drop off in performance is very noticable.  Even with stat=0, only in a very specific usage scenario (described below) does it actually nullify phar vs. filesystem.


Without APC
-----------

    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-filesys.php

    Requests per second:    111.19 [#/sec] (mean)
    Time per request:       8.994 [ms] (mean)


    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-phar.php

    Requests per second:    87.49 [#/sec] (mean)
    Time per request:       11.429 [ms] (mean)


Then APC 3.1.13 (Beta) is introduced, the app is clearly faster on the filesystem, but not nearly as fast one would expect in the phar version:


With APC (enabled=1, stat=1)
----------------------------

    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-filesys.php

    Requests per second:    474.47 [#/sec] (mean)
    Time per request:       2.108 [ms] (mean)


    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-phar.php

    Requests per second:    238.84 [#/sec] (mean)
    Time per request:       4.187 [ms] (mean)


With APC (enabled=1, stat=0)
----------------------------

    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-filesys.php

    Requests per second:    529.02 [#/sec] (mean)
    Time per request:       1.890 [ms] (mean)


    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-phar.php

    Requests per second:    246.71 [#/sec] (mean)
    Time per request:       4.053 [ms] (mean)




With APC (enabled=1, stat=0) (With include_path hack)
-----------------------------------------------------

If this line:

    require '../vendor/Zend_Db-2.1.0beta1.phar';

is swapped out with this (to ensure the require is relative to include):

    set_include_path('.:' . realpath(__DIR__ . '/../vendor'));
    require 'Zend_Db-2.1.0beta1.phar';

I get the following:

    $ ab -H "Connection: close" -n 500 http://test.dev/index-with-phar.php

    Requests per second:    522.68 [#/sec] (mean)
    Time per request:       1.913 [ms] (mean)



Can someone help me make sense of all this?  Here are my questions:

  * The phar files are listed in the apc\_cache\_info() with >1 hits, are the stat's to those phar files really much slower than to filesystem files?

  * Why does the include_path trick affect performance with regards to phar files?

