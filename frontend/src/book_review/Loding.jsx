import React from "react";

import { Dimmer, Loader } from "semantic-ui-react";

export const Loading = () => {
    const inverted = true;
    const content="Loading...";
    return (
        < Dimmer inverted={inverted} active={true}>
            < Loader content={content}/>
        </ Dimmer>
    )
}
    
export default Loading;