import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import PropTypes from 'prop-types';
import { useUrl } from '../../useUrl.jsx';
import Loading from '../Loading.jsx';
import '../create_book_review/CreateBookReview.scss';
import '../edit_book_review/EditBookReview.jsx';
import './BookReviewInput.scss';

export const BookReviewInput = (props) => {
  const { BookId } = useParams(); //クエリパラメータを取得するには [] ではなく {} で囲わなければならない（ややこしい...）
  const getBookDetailUrl = useUrl('bookDetailOperation', BookId);
  const [cookies] = useCookies();
  const navigate = useNavigate();
  const [bookData, setBookData] = useState([]);
  const [errorMessage, setErrorMessage] = useState('');
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm(); // バリデーションのフォームを定義。

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  useEffect(() => {
    if (!BookId) return; //書籍の新規作成ページは書籍のデータを取得する必要がないのですぐにreturn

    axios
      .get(getBookDetailUrl, { headers })
      .then((response) => {
        if (!response.data.is_mine) return navigate('/'); //自分の書いた書籍レビューじゃなかったらホーム画面に遷移する
        setBookData(response.data);
      })
      .catch((error) => {
        setErrorMessage(`ユーザー情報取得に失敗しました ${error}`);
      });
  }, []);

  const handleTitleChange = (event) => props.bookTitle.current = event.target.value;
  const handleDetailChange = (event) => props.bookDetail.current = event.target.value;
  const handleReviewChange = (event) => props.bookReview.current = event.target.value;

  return (
    <div className="float_component">
      <h2 className="error-massage">{errorMessage}</h2>
      {Object.keys(bookData).length === 0  && props.isCreateBook === false ? (
        <Loading />
      ) : (
        <form onSubmit={handleSubmit(props.BookOperations)} className="book_operation">
          <label className="title">
            タイトル
            <br />
            <input
              type="text"
              {...register('title', { required: true })}
              onChange={handleTitleChange}
              defaultValue={bookData.title}
              className="input_title"
            />
          </label>
          <p>
            {errors.title?.type === 'required' && (
              <b className="input-error-message">※タイトルを入力してください。</b>
            )}
          </p>
          <label className="detail_info">
            書籍の詳細情報
            <br />
            <textarea
              {...register('detail', { required: true })}
              onChange={handleDetailChange}
              defaultValue={bookData.detail}
              className="input_detail"
            />
          </label>
          <p>
            {errors.detail?.type === 'required' && (
              <b className="input-error-message">※書籍の詳細情報を入力してください。</b>
            )}
          </p>
          <label className="review">
            書籍のレビュー
            <br />
            <textarea
              {...register('review', { required: true })}
              onChange={handleReviewChange}
              defaultValue={bookData.review}
              className="input_review"
            />
          </label>
          <p>
            {errors.review?.type === 'required' && (
              <b className="input-error-message">※書籍レビューを入力してください。</b>
            )}
          </p>
          <div className="checkbox">
            <label>
              <input
                type="checkbox"
                checked={props.isSpoiler}
                onChange={() => props.setIsSpoiler(!props.isSpoiler)}
              />
              ネタバレあり
            </label>
          </div>
          <br />
          {props.isCreateBook === true ? (
            <button type="submit" className="createBook__button">
              作成
            </button>
          ) : (
            <div>
              <button onClick={props.deleteBook} className="delete-button">
                削除
              </button>
              <button type="submit" className="EditBook__button">
                更新
              </button>
            </div>
          )}
        </form>
      )}
    </div>
  );
};

BookReviewInput.propTypes = {
  isSpoiler: PropTypes.bool,
  isCreateBook: PropTypes.bool,
  bookTitle: PropTypes.func.isRequired,
  bookDetail: PropTypes.func.isRequired,
  bookReview: PropTypes.func.isRequired,
  setIsSpoiler: PropTypes.func.isRequired,
  BookOperations: PropTypes.func.isRequired,
  deleteBook: PropTypes.func.isRequired,
  bookData: PropTypes.shape({
    title: PropTypes.string,
    detail: PropTypes.string,
    review: PropTypes.string
  })
};
