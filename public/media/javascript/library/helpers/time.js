/**
 * Use new Date().getTimestamp() instead
 * @deprecated
 * @author vpriem
 * @since 25.08.2011
 * @return int
 */
function time(){
    
    return Math.round(new Date().getTime() / 1000);
}
