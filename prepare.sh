#!/bin/bash

r='\e[0;33m'
s='\e[0;32m'
b='\e[1m'
e='\e[0m'

showHelp(){
    echo -e "
    
  $b Input arguments: $e
        
    $r --repository $e *  
        Repository name. --repository=Fiv
        
    $r --package $e *      
        Package name. --package=Parser
                 
    $s --description $e
        Deskription of package
         
    $s --repo_path $e
        Repository path. By default it is combined with --repository and --package in lower case and with some manipulations
        --repo-path=fiv/parser
          
    $s --author_github_name $e
        Author github name. 
                    
    $s --author_name $e
        Author Name. By default script try to get it from git global configuration
                 
    $s --author_email $e
        Author email. By default script try to get it from git global configuration
                        
    $s --author_website $e
        Link to website. --author_website=http://funivan.com
                  
    $s --year $e
        You can specify year when package was created. By default cuurrent year
    
    $s -h, --help $e 
        Show help
        
    $s --no-interactive $e
        Disable interactive mode. By default interactive mode is on. 
                       

  $b How it works $e
    Remove .git directory
    Replace all options in files
    Remove self     
  $b Exit codes: $e
    0 - Sucess
    1 - Not Enough Input Arguments 
    2 - Canceled 
    "
}

year=`date +"%Y"`
interactive="yes"

for i in "$@"; do
    case $i in
    
    --repository=*)
        repository="${i#*=}"
    shift;;
    
    --package=*)
        package="${i#*=}"
    shift;;
    
    --description=*)
        description="${i#*=}"
    shift;;
    
    --repo_path=*)
        repo_path="${i#*=}"
    shift;;
    
    --author_github_name=*)
        author_github_name="${i#*=}"
    shift;;
    
    --author_name=*)
        author_name="${i#*=}"
    shift;;
    
    --author_email=*)
        author_email="${i#*=}"
    shift;;
    
    --author_website=*)
        author_website="${i#*=}"
    shift;;
    
    --year=*)
        year="${i#*=}"
    shift;;
    
    -h|--help)
        showHelp
        exit 0;
    shift;;
    
    --no-interactive)
        interactive="no"
    shift;;
    
    
    *)
       # unknown option
    ;;
    esac
done


if [[ -z "$repository" || -z "$package" ]]; then 
  echo "Pleas specify --repository and --package";
  exit 1;
fi;


if [[ -z "$repo_path" ]]; then
  repo_path_first=`echo $repository | sed 's/\(.\)\([A-Z]\)/\1-\2/g'` 
  repo_path_second=`echo $package | sed 's/\(.\)\([A-Z]\)/\1-\2/g'` 
  repo_path=`echo $repo_path_first/$repo_path_second | sed 's/--/-/g'`
fi

repo_path=`echo $repo_path | tr '[:upper:]' '[:lower:]'`


if [[ -z "$author_name" ]]; then
  author_name=`git config --global --get user.name`
fi
if [[ -z "$author_email" ]]; then
  author_email=`git config --global --get user.email`
fi





declare -a variables=("repository" "package"  "description" "repo_path" "author_github_name" "author_name" "author_email" "author_website" "year")
padlength=30
pad=$(printf '%0.1s' " "{1..60})

for var in "${variables[@]}"; do
  printf '%s' "$var"
  printf '%*.*s' 0 $((padlength - ${#var})) "$pad"
  echo " = ${!var}";
done

 RUN="y"
 if [[ "$interactive" == "yes" ]]; then
    read -p "Prepare project (y/n): " RUN
 fi;
 
 if [[ "$RUN" != "y" ]]; then
    echo "Canceled"
    exit 2;
 fi;
 
 
 

  rm -rf .git/
  
  for i in `find . -type f`;     do
    for var in "${variables[@]}"; do
      from="\:$var";
      to="${!var}";
      sed -i "s~$from~$to~g" $i;
    done
  done

  rm -- "$0"
