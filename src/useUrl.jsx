import React from "react";

export const useUrl = (props) => {
    const endpoint = process.env.REACT_APP_API_URL;

    if(props === "get_books"){
        const url = `${endpoint}/books`;
        return url;
    }

    if(props === "get_public_books" ){
        const url = `${endpoint}/public/books`;
        return url;
    } 

    // if(props ===  ) return ;

    // if(props === ) return ;

    // if(props === ) return ;

    // if(props === ) return ;

    // if(props === ) return ;

    // if(props === ) return ;

    // if(props === ) return ;

    // if(props === ) return ;

    // if(props === ) return ;
}