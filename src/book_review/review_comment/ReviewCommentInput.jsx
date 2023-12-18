import React, { useState } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import PropTypes from 'prop-types';
import { url } from '../../const';
import './ReviewCommentInput.scss';

export const ReviewCommentInput = (props) => {
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm(); // バリデーションのフォームを定義。
  const [comment, setComment] = useState('');
  const { BookId } = useParams(); //クエリパラメータを取得するには[]ではなく{}で囲わなければならない
  const [cookies] = useCookies();
  const handleCommentChange = (e) => setComment(e.target.value);

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  const create_comment = () => {
    axios.post(`${url}/books/${BookId}/comment`, { comment: comment }, { headers })
         .then((response) => {
          props.setBookComment([...props.bookComment, response.data]);
         });
  };

  return (
    <div>
      <form onSubmit={handleSubmit(create_comment)}>
        <div>
          <textarea
            className="input_comment"
            placeholder="コメントを入力"
            {...register('textarea', { required: true })}
            onChange={handleCommentChange}
          />
          <p>{errors.textarea?.type === 'required' && <b className="error-message">※コメントを入力してください。</b>}</p>
          <br />
          <button type="submit" className="comment_button">
            コメント
          </button>
        </div>
      </form>
    </div>
  );
};

ReviewCommentInput.propTypes = {
  setBookComment: PropTypes.func.isRequired,
  bookComment: PropTypes.array
};
