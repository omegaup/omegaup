If you want to run a contest at your school, make sure that the following domains are accessible from the computers that the contestants will use:

* **One of omegaUp’s addresses:**
  * https://arena.omegaup.com - if you want to use **lockdown mode** (make sure not to allow the other one, or lockdown mode won’t work).
  * https://omegaup.com - if you want to use **normal mode**
* https://ssl.google-analytics.com

### The following are optional:

* https://secure.gravatar.com (Optional, displays the avatar in the top-right corner)
* https://accounts.google.com (Optional, to log in via Google)
* https://connect.facebook.net and https://s-static.*.facebook.com (Optional, to log in via Facebook)

All of them use **HTTPS** connections, so only **port 443** is required.
Connections through **port 80** will not work because they are automatically redirected to HTTPS.
Also, make sure to configure the firewall to use **DENY** instead of **DROP**. Otherwise, the browser will try to connect to the domains listed above, and when it doesn’t receive a response, it will wait for 20–30 seconds before giving up — causing the page to load very slowly.

## Lockdown Mode

If instead of using the normal domain you connect through https://arena.omegaup.com/, you will enter **lockdown mode**.
This mode is designed to provide stronger guarantees that students cannot exchange information through the platform.
Much of the site’s functionality is restricted, and to preserve the integrity of the lockdown, **no exceptions can be made** to these restrictions under any circumstance.

The most common features that are restricted in lockdown mode include:

* **Admin Mode**
* **Practice mode**
* **Viewing source code of previous submissions** (a message error is shown instead)

Again, if your situation requires any of the features that are blocked in lockdown mode to work, do not use lockdown mode and instead connect through https://omegaup.com.

## Operating System

We use **Ubuntu 14.04** to grade submissions, so any relatively recent Linux distribution should be **100% compatible** with our evaluation environment. Windows has some issues, such as using `%I64d` to print long long values of `%lld`, and many Windows editors include the `conio.h` file (which does not exist on Linux).

## Large Groups and Other Considerations

If you plan to run a large contest with many participants (100 or more contestants) connecting at the same time, please **notify us in advance** to ensure we have the capacity to serve your contest on that day.