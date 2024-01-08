import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { IconContext } from 'react-icons';
import { BsHeart } from 'react-icons/bs';
import { FaHeart } from 'react-icons/fa';
import { FaCheckCircle } from 'react-icons/fa';
import axios from 'axios';
import PropTypes from 'prop-types';
import { useUrl } from '../../useUrl';
import './ReviewCommentInput.scss';

export const ReviewCommentInput = (props) => {
  //useFormのregisterとかに名前つけれるらしい
  const { register: sendCommentRegister, handleSubmit: sendCommentSubmit, formState: sendCommentFormState } = useForm();
  const { register: editCommentRegister, handleSubmit: editCommentSubmit, formState: editCommentFormState } = useForm();
  const [BookComment, setBookComment] = useState([]);
  const [UpdateComment, setUpdateComment] = useState(false);
  const [commentPage, setCommentPage] = useState(0);
  const [isEditComment, setIsEditComment] = useState(false);
  const get_comment_url = useUrl('comment_operation', props.BookId); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const create_comment_url = useUrl('comment_operation', props.BookId);
  const good_operation_url = useUrl('good_operation');
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
    axios
      .get(get_comment_url, {
        headers,
        params: {
          comment_offset: commentPage
        }
      })
      .then((response) => {
        setBookComment(response.data);
      });
  }, [UpdateComment, commentPage]);

  const sendComment = (event) => {
    const comment = event.comment;
    axios.post(create_comment_url, { comment: comment }, { headers }).then(() => {
      //初期値「false」のUpdateCommentの否定をstateに入れてあげることでapiを再度呼び出す
      setUpdateComment(!UpdateComment);
    });
  };

  const editComment = (data, book_comment_id) => {
    const comment = data.edit_comment_input;
    const edit_comment_url = useUrl('comment_operation', book_comment_id);
    axios.patch(edit_comment_url, { comment: comment }, { headers }).then(() => {
      setUpdateComment(!UpdateComment);
      setIsEditComment(false);
    });
  };

  const deleteComment = (book_comment_id) => {
    const delete_comment_url = useUrl('comment_operation', book_comment_id);
    axios.delete(delete_comment_url, { headers }).then(() => {
      setUpdateComment(!UpdateComment);
    });
  };

  const fluctuationLikes = (likes_count_change, comment_id) => {
    // 以前の状態を基に新しいオブジェクトを作成
    const newCommentLikes = { ...commentLikes };

    // 特定のコメントのいいね状態を切り替える
    newCommentLikes[comment_id] = !newCommentLikes[comment_id];

    // 状態を更新
    setCommentLikes(newCommentLikes);
    axios.post(good_operation_url, { likes: likes_count_change, comment_id: comment_id }, { headers }).then(() => {
      setUpdateComment(!UpdateComment);
    });
  };

  return (
    <div>
      <form onSubmit={sendCommentSubmit(sendComment)}>
        <p>{sendCommentFormState.errors.comment?.type === 'required' && <b className="comment-error-message">※コメントを入力してください。</b>}</p>
        <textarea className="input_comment" {...sendCommentRegister('comment', { required: true })} placeholder="コメントを入力" />
        <br />
        <button type="submit" className="comment_button">
          コメント
        </button>
      </form>
      <h2 className="h2_comment">コメント欄</h2>
      <ul>
        {BookComment.map((BookCommentList, key) => (
          <li key={key} value={BookCommentList.id} className="comment_list">
            <img src={BookCommentList.user_image_url} alt="ユーザーのアイコン" className="comment_userIcon" />
            {BookCommentList.user_name}
            {BookCommentList.is_reviewer ? (
              <IconContext.Provider value={{ color: '#000000', size: '17px' }}>
                <FaCheckCircle className="reviewer" />
              </IconContext.Provider>
            ) : (
              <></>
            )}
            {BookCommentList.is_your_comment ? (
              <span className="comment_operation_container">
                <span className="comment_operation" onClick={() => setIsEditComment(BookCommentList.id)}>
                  編集
                </span>
                <span className="comment_operation" onClick={() => deleteComment(BookCommentList.id)}>
                  削除
                </span>
              </span>
            ) : (
              <></>
            )}
            <br />
            {isEditComment === BookCommentList.id ? (
              <form onSubmit={editCommentSubmit((data) => editComment(data, BookCommentList.id))}>
                <p>
                  {editCommentFormState.errors.edit_comment_input?.type === 'required' && (
                    <b className="comment-error-message">※コメントを入力してください。</b>
                  )}
                </p>
                <textarea className="edit_comment" {...editCommentRegister('edit_comment_input', { required: true })} placeholder="コメントを入力" />
                <br />
                <button className="cancel_button" onClick={() => setIsEditComment(false)}>
                  キャンセル
                </button>
                <button className="edit_button" type="submit">
                  更新
                </button>
              </form>
            ) : (
              <>
                <p className="user_comment">{BookCommentList.comment}</p>
                <div className="likes">
                  <IconContext.Provider value={{ color: '#ff69b4', size: '20px' }}>
                    {commentLikes[BookCommentList.id] ? (
                      <FaHeart className="likes-icon" onClick={() => fluctuationLikes(-1, BookCommentList.id)} />
                    ) : (
                      <BsHeart className="likes-icon" onClick={() => fluctuationLikes(1, BookCommentList.id)} />
                    )}
                  </IconContext.Provider>
                  <span className="likes-count">{BookCommentList.comment_likes}</span>
                </div>
              </>
            )}
          </li>
        ))}
      </ul>
      <div className="comment-pagenation">
        {commentPage !== 0 ? (
          <button
            id="before"
            onClick={() => {
              setCommentPage(commentPage - 10);
            }}
            className="comment-pagenation__button"
          >
            前のページへ
          </button>
        ) : (
          <button className="comment-pagenation__button" disabled>
            前のページへ
          </button>
        )}
        {BookComment.length === 10 ? (
          <button
            id="next"
            onClick={() => {
              setCommentPage(commentPage + 10);
            }}
            className="comment-pagenation__button"
          >
            次のページへ
          </button>
        ) : (
          <button className="comment-pagenation__button" disabled>
            次のページへ
          </button>
        )}
      </div>
    </div>
  );
};

ReviewCommentInput.propTypes = {
  BookId: PropTypes.string
};
