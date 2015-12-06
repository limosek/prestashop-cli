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

__object2objects()
{
    ./psobjects | grep " $1" | cut -d ' ' -f 1
}

__props()
{
    ./psprops "$1"
}

_pslist() 
{
    local obj prop cur
    COMPREPLY=()
    obj="${COMP_WORDS[1]}"
    prop="${COMP_WORDS[2]}"
    cur="${COMP_WORDS[COMP_CWORD]}"

    case "$COMP_CWORD" in
1)	COMPREPLY=( $(compgen -W "${PSOPTS} $(__objects)" -- ${cur}) )
	;;
*)	COMPREPLY=( $(compgen -W "$(__props $(__objects2object $obj))" -- ${cur}) )
	;;
    esac
}
complete -F _pslist pslist

_psget() 
{
    local obj id prop cur
    COMPREPLY=()
    obj="${COMP_WORDS[1]}"
    id="${COMP_WORDS[2]}"
    prop="${COMP_WORDS[3]}"
    cur="${COMP_WORDS[COMP_CWORD]}"

    case "$COMP_CWORD" in
1)	COMPREPLY=( $(compgen -W "${PSOPTS} $(__object)" -- ${cur}) )
	;;
2)	COMPREPLY=( $(compgen -W "$(pslist $(__object2objects $obj))" -- ${cur}) )
	;;
*)	COMPREPLY=( $(compgen -W "$(__props $obj)" -- ${cur}) )
	;;
    esac
}
complete -F _psget psget
complete -F _psget psupdate

_psobj() 
{
    local obj id prop cur
    COMPREPLY=()
    obj="${COMP_WORDS[1]}"
    id="${COMP_WORDS[2]}"
    prop="${COMP_WORDS[3]}"
    cur="${COMP_WORDS[COMP_CWORD]}"

    case "$COMP_CWORD" in
1)	COMPREPLY=( $(compgen -W "${PSOPTS} $(__object)" -- ${cur}) )
	;;
2)	COMPREPLY=( $(compgen -W "$(pslist $(__object2objects $obj))" -- ${cur}) )
	;;
    esac
}
complete -F _psobj psdisable
complete -F _psobj psenable
complete -F _psobj psdelete
complete -F _psobj psprops

