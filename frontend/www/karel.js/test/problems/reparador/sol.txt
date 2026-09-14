class program {
    void east(){
        while (notFacingEast) turnleft();
    }
    
    void north(){
        while (notFacingNorth) turnleft();
    }
    
    void south(){
        while (notFacingSouth) turnleft();
    }
    
    void west(){
        while (notFacingWest) turnleft();
    }
    
    void bumpToBeeper(){
        while (frontIsClear && notNextToABeeper) move();
    }
    
    program () {
        east();
        bumpToBeeper();
        
        north();
        move();
        if (nextToABeeper){
            south();
            move();
            west();
            move();
            putbeeper();
            turnoff();
        } else {
            south();
            move();
        }
        
        while (nextToABeeper){
            east();
            move();
            north();
            move();
        }
        
        south();
        
        while (frontIsClear){
            putbeeper();
            move();
        }
        
        putbeeper();
    }
}
