analysis
========

VB program to merge a specific set of TXT files to CSV for thesis.

Quickfix solutions from PHP.

## PHP Version
In order to run this you will need a webserver running on your PC.

The simplest solution is to install WAMP (Windows Apache Mysql and PHP)
```
start wampserver
```

Test WAMP by opening you browser and navigating to http://localhost/

Next we need to make sure the analysis files are in the right directory. If WAMP is 
being used then the default directory for your files is
```
C:\wamp\www\
```

Open the program GIT Bash on your PC and navigate to the WAMP directory with the following command
```
cd /c/wamp/www
```

If this is your first time using this repository then you will need to clone it first
```
git clone git@github.com:Akahadaka/analysis.git
```

Once you have a cloned repository, then just navigate into the analysis folder
```
cd analysis
```

Now checkout the version you want to test with, e.g.
```
git checkout test_cu
```
or
```
git checkout test_cu_edg
```

Alternatively make a new test branch
```
git checkout master
git checkout -b my_new_test_cu
```

Now in windows upload your test files to any folder inside the repo, e.g.
```
C:\wamp\www\analysis\test\Cu
```
and run the program from your browser

http://localhost/analysis/

Select the localtion of your files, e.g.
```
test/Cu/
```

and the location of your output files (note the directory needs to exist already), e.g.
```
test/
```

Once done you can go back to GIT Bash and commit your tests for future use
```
git push --set-upstream origin my_new_test_cu
```

The End.
