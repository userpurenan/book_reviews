import React from "react";


export const ReviewComment = () => {
    return (
        <div>
            <form>
                <textarea className="input_comment" />
                <br />
                <button type="submit" className="comment_button">コメント</button>
            </form>
        </div>
    )
}