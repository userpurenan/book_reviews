import React, { useEffect, useState } from "react";
import axios from "axios";
import PropTypes from 'prop-types';
import { useCookies } from "react-cookie";
import { url } from "../../const";
import './ReviewCommentInput.scss';
import { useForm } from "react-hook-form";

export const ReviewCommentInput = (props) => {
    const {
        register,
        handleSubmit,
        formState: { errors }
      } = useForm(); // バリデーションのフォームを定義。
    
    const [BookComment, setBookComment] = useState([]);
    const [UpdateComment, setUpdateComment] = useState();
    const [cookies] = useCookies();

    const headers = {
        authorization: `Bearer ${cookies.token}`
    }

    useEffect(() => {
        axios.get(`${url}/books/${props.BookId}/comment`, { headers })
             .then((response) => {
                setBookComment(response.data)
             });
    },[UpdateComment]);

    const sendComment = (event) => {
        const comment = event.comment;
        axios.post(`${url}/books/${props.BookId}/comment`, {comment: comment}, {headers})
             .then(() => {
                setUpdateComment(Math.random());
             });
    }

    return (
        <div>
            <form onSubmit={handleSubmit(sendComment)}>
                <p>{errors.comment?.type === 'required' && <b className="comment-error-message">※コメントを入力してください。</b>}</p>
                <textarea 
                    className="input_comment" 
                    {...register('comment', { required: true })}
                    placeholder="コメントを入力して下さい"
                />
                <br />
                <button type="submit" className="comment_button">コメント</button>
            </form>
            <ul>
                {BookComment.map((BookCommentList, key) => (
                    <li key={key} value={BookCommentList.id} className='comment_list'>
                        {BookCommentList.user_name}<img src={BookCommentList.user_imageUrl} alt="ユーザーのアイコン" className="userIcon" /><br />
                        <p className='user_comment'>{BookCommentList.comment}</p>
                    </li>            
                ))}
            </ul>
        </div>
    )
}

ReviewCommentInput.propTypes = {
    BookId: PropTypes.string
}