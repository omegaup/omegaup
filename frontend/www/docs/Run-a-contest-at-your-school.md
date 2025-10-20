If you want to run a contest at your school, make sure the following domains are accessible from the machines that contestants will use:

* One of the omegaUp addresses:
  * https://arena.omegaup.com if you want to use **lockdown mode** (make sure to block access to the other one, otherwise lockdown mode will not work properly).
  * https://omegaup.com if you want to use **normal mode**
* https://ssl.google-analytics.com

The following are optional:

* https://secure.gravatar.com (Optional, displays the user avatar in the top-right corner)
* https://accounts.google.com (Optional, for logging in via Google)
* https://connect.facebook.net and https://s-static.*.facebook.com (Optional, for logging in via Facebook)

All of these use HTTPS connections, so only port 443 is required. Connections on port 80 will not work since they are automatically redirected to HTTPS.  
Also, make sure your firewall is configured to use DENY instead of DROP. Otherwise, the browser will attempt to connect to the domains listed above, and without a response it will wait 20–30 seconds before timing out, causing the page to load very slowly.


## Lockdown Mode

If instead of using the normal domain you connect through https://arena.omegaup.com/, you’ll enter lockdown mode.  
This mode is designed to provide stronger guarantees that students cannot exchange information through the platform.  
Much of the site’s functionality is restricted, and to maintain the integrity of the lockdown, no exceptions can be made. Commonly restricted features in lockdown mode include:

* Admin mode  
* Practice mode  
* Viewing past submissions’ source code (an error message is shown instead)

Again, if your situation requires any of the features blocked in lockdown mode to function, do not use lockdown mode and connect through https://omegaup.com instead.


## Operating System

We use Ubuntu 14.04 to grade submissions, so any relatively recent Linux distribution should be 100% compatible with our evaluation environment.  
Windows has some issues, such as using `%I64d` instead of `%lld` for printing *long long* values, and many Windows editors include the header file `conio.h` (which does not exist in Linux).


## Large Groups and Other Considerations

If you plan to host a large contest with many participants (100 or more), please let us know in advance to ensure we have enough capacity to serve your contest on that day.

