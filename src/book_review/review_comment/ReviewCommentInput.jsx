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
  const getCommentUrl = useUrl('commentOperation', props.BookId); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const createCommentUrl = useUrl('commentOperation', props.BookId);
  const UpdateLikesUrl = useUrl('updateLikes');
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
      .get(getCommentUrl, {
        headers,
        params: {
          comment_offset: commentPage
        }
      })
      .then((response) => {
        setBookComment(response.data);
      })
      .catch((error) => {
        alert(`コメントの取得に失敗しました。${error}`);
      });
  }, [UpdateComment, commentPage]);

  const sendComment = (event) => {
    const comment = event.comment;
    axios
      .post(createCommentUrl, { comment: comment }, { headers })
      .then(() => {
        //初期値「false」のUpdateCommentの否定をstateに入れてあげることでapiを再度呼び出す
        setUpdateComment(!UpdateComment);
      })
      .catch((error) => {
        alert(`コメントの作成に失敗しました。${error}`);
      });
  };

  const editComment = (data, bookCommentId) => {
    const comment = data.edit_comment_input;
    const editCommentUrl = useUrl('commentOperation', bookCommentId);
    axios
      .patch(editCommentUrl, { comment: comment }, { headers })
      .then(() => {
        setUpdateComment(!UpdateComment);
        setIsEditComment(false);
      })
      .catch((error) => {
        alert(`コメントの編集に失敗しました。${error}`);
      });
  };

  const deleteComment = (bookCommentId) => {
    const deleteCommentUrl = useUrl('commentOperation', bookCommentId);
    axios
      .delete(deleteCommentUrl, { headers })
      .then(() => {
        setUpdateComment(!UpdateComment);
      })
      .catch((error) => {
        alert(`コメントの削除に失敗しました。${error}`);
      });
  };

  const updateLikes = (likesCountChange, commentId) => {
    // 以前の状態を基に新しいオブジェクトを作成
    const newCommentLikes = { ...commentLikes };

    // 特定のコメントのいいね状態を切り替える
    newCommentLikes[commentId] = !newCommentLikes[commentId];

    // 状態を更新
    setCommentLikes(newCommentLikes);
    axios.post(UpdateLikesUrl, { likes: likesCountChange, comment_id: commentId }, { headers }).then(() => {
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
                      <FaHeart className="likes-icon" onClick={() => updateLikes(-1, BookCommentList.id)} />
                    ) : (
                      <BsHeart className="likes-icon" onClick={() => updateLikes(1, BookCommentList.id)} />
                    )}
                  </IconContext.Provider>
                  <span className="likes-count">{BookCommentList.comment_likes}</span>
                </div>
              </>
            )}
          </li>
        ))}
      </ul>
      <CommentPagenation commentPage={commentPage} setCommentPage={setCommentPage} BookCommentLength={BookComment.length} />
    </div>
  );
};

export const CommentPagenation = (props) => {
  return (
    <div className="comment-pagenation">
      {props.commentPage !== 0 ? (
        <button
          id="before"
          onClick={() => {
            props.setCommentPage(props.commentPage - 10);
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
      {props.BookCommentLength === 10 ? (
        <button
          id="next"
          onClick={() => {
            props.setCommentPage(props.commentPage + 10);
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
  );
};

ReviewCommentInput.propTypes = {
  BookId: PropTypes.string
};

CommentPagenation.propTypes = {
  commentPage: PropTypes.number,
  setCommentPage: PropTypes.func.isRequired,
  BookCommentLength: PropTypes.number
};
