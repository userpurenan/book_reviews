import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { IconContext } from 'react-icons';
import { BsHeart } from 'react-icons/bs';
import { FaHeart } from 'react-icons/fa';
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

  const [BookComment, setBookComment] = useState([]);
  const [UpdateComment, setUpdateComment] = useState();
  const [commentLikes, setCommentLikes] = useState(() => {
    // localStorageからデータを取得し、存在しない場合はデフォルトで空のオブジェクトを返す
    const storedData = localStorage.getItem('commentLikes');
    return storedData ? JSON.parse(storedData) : {};
  });
  const [cookies] = useCookies();

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  useEffect(() => {
    // コメントのいいね状態が変更されたらlocalStorageに保存する
    localStorage.setItem('commentLikes', JSON.stringify(commentLikes));
  }, [commentLikes]);

  useEffect(() => {
    axios.get(`${url}/books/${props.BookId}/comment`, { headers }).then((response) => {
      setBookComment(response.data);
    });
  }, [UpdateComment]);

  const sendComment = (event) => {
    const comment = event.comment;
    axios.post(`${url}/books/${props.BookId}/comment`, { comment: comment }, { headers }).then(() => {
      //updateCommentに前とは違う数値を入れることで、sendCommentを呼び出すたびにuseEffectを実行できる。
      //低確率で前に入っていた数値と同じ数値が入る
      setUpdateComment(Math.random());
    });
  };

  const fluctuationLikes = (likes_count_change, comment_id) => {
    // 以前の状態を基に新しいオブジェクトを作成
    const newCommentLikes = { ...commentLikes };

    // 特定のコメントのいいね状態を切り替える
    newCommentLikes[comment_id] = !newCommentLikes[comment_id];

    // 状態を更新
    setCommentLikes(newCommentLikes);
    axios
      .post(`${url}/comment/fluctuationLikes`, { likes: likes_count_change, comment_id: comment_id }, { headers })
      .then(() => {
        setUpdateComment(Math.random());
      });
  };

  return (
    <div>
      <form onSubmit={handleSubmit(sendComment)}>
        <p>
          {errors.comment?.type === 'required' && (
            <b className="comment-error-message">※コメントを入力してください。</b>
          )}
        </p>
        <textarea className="input_comment" {...register('comment', { required: true })} placeholder="コメントを入力" />
        <br />
        <button type="submit" className="comment_button">
          コメント
        </button>
      </form>
      <h2 className="h2_comment">コメント欄</h2>
      <ul>
        {BookComment.map((BookCommentList, key) => (
          <li key={key} value={BookCommentList.id} className="comment_list">
            {BookCommentList.user_name}
            <img src={BookCommentList.user_imageUrl} alt="ユーザーのアイコン" className="comment_userIcon" />
            <br />
            <p className="user_comment">{BookCommentList.comment}</p>
            <IconContext.Provider value={{ color: '#ff69b4', size: '20px' }}>
              {commentLikes[BookCommentList.id] ? (
                <FaHeart className="likes" onClick={() => fluctuationLikes(-1, BookCommentList.id)} />
              ) : (
                <BsHeart className="likes" onClick={() => fluctuationLikes(1, BookCommentList.id)} />
              )}
            </IconContext.Provider>
            <span className="count_likes">{BookCommentList.comment_likes}</span>
          </li>
        ))}
      </ul>
    </div>
  );
};

ReviewCommentInput.propTypes = {
  BookId: PropTypes.string
};
