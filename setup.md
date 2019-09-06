
# For MAC and Linux
## Create ssh keys (Locally)
### Generate key
`cd ~/.ssh || mkdir ~/.ssh`
`ssh-keygen -t rsa -b 4096 -C "DreamHost Git repo"`

Enter ${name} for key when prompted 

### Add your key to dreamhost server
`cat ~/.ssh/${name}.pub | ssh bigbio@173.236.184.116 "mkdir ~/.ssh; cat >> ~/.ssh/auth`

### Confirm key
`ssh bigbio@173.236.184.116`
`cat ~/.ssh/authorized_keys`

## Setup Local Repo
### Clone the repo 
`git clone ssh://bigbio@173.236.184.116/~/bigbio.org/dev`

### Configure remote
`git remote add dreamhost ssh://bigbio@173.236.184.116/~/bigbio.org/dev` 
This sets up the remote repo 'dreamhost' which is located in the dev folder at the end of this path. 
The remote repo isn't a 'working tree' meaning you can't work from it, so you can only push from your
local machine. 

`git push -u dreamhost master`
On push to dreamhost, the dev repo gets all the files from your local machine where they are redirected to the working tree (set as /beta)

You probably won't be using this command directly, because we'll configure origin to push to both the remote and github, but now it's saved 
### Configure push to github
`git remote add github https://github.com/Big-Bio/wp_site-.git`
Origin will allow us to push to both URLs 
`git remote add origin https://github.com/Big-Bio/wp_site-.git`
`git remote set-url --add --push origin https://github.com/Big-Bio/wp_site-.git`
`git remote set-url --add --push origin ssh://bigbio@173.236.184.116/~/bigbio.org/dev`

As an example, if you push a change
`git push origin master` updates will be seen on both /beta and the github repo

`git remote show origin` should show you these results

```remote origin
Fetch URL: https://github.com/Big-Bio/wp_site-.git
Push  URL: https://github.com/Big-Bio/wp_site-.git
Push  URL: ssh://bigbio@173.236.184.116/~/bigbio.org/dev
HEAD branch: master
Remote branch:
master new (next fetch will store in remotes/origin)
Local ref configured for 'git push':
master pushes to master (local out of date)
```

