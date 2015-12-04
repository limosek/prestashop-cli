echo $PATH | grep -vq $PWD && export PATH=$PWD:$PATH

PSOPTS="--help --debug --dry --output-format --progress --buffered --language --base64"

__objects()
{
   ./psobjects | cut -d ' ' -f 1
}

__object()
{
   ./psobjects | cut -d ' ' -f 2
}

__objects2object()
{
    ./psobjects | grep ^$1 | cut -d ' ' -f 2
}

__props()
{
    ./psprops "$1"
}

_pslist() 
{
    local cur prev opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    if ! __objects | grep -q $prev; then
        COMPREPLY=( $(compgen -W "${PSOPTS} $(__objects)" -- ${cur}) )
    else
        COMPREPLY=( $(compgen -W "$(__props $(__objects2object $prev))" -- ${cur}) )
    fi
}
complete -F _pslist pslist

_psget() 
{
    local cur prev opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    if ! __objects | grep -q $prev; then
        COMPREPLY=( $(compgen -W "${PSOPTS} $(__object)" -- ${cur}) )
    else
        COMPREPLY=( $(compgen -W "$(__props $prev)" -- ${cur}) )
    fi
}
complete -F _psget psget
complete -F _psget psupdate

_psobj() 
{
    local cur prev opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    
    COMPREPLY=( $(compgen -W "${PSOPTS} $(__object)" -- ${cur}) )
}
complete -F _psobj psdisable
complete -F _psobj psenable
complete -F _psobj psdelete
complete -F _psobj psprops
