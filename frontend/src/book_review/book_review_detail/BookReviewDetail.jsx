import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { useUrl } from '../../useUrl';
import Loading from '../Loading';
import { Header } from '../header/Header';
import { ReviewCommentInput } from '../review_comment/ReviewCommentInput';
import './BookReviewDetail.scss';

export const BookReviewDetail = () => {
  const { BookId } = useParams(); //クエリパラメータを取得するには[]ではなく{}で囲わなければならない
  const [cookies] = useCookies();
  const [isLoading, setIsLoading] = useState(false);
  const [bookData, setBookData] = useState('');
  const getBookDetailUrl = useUrl('bookDetailOperation', BookId); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const [errorMessage, setErrorMessage] = useState('');

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  useEffect(() => {
    setIsLoading(true);

    axios
      .get(getBookDetailUrl, { headers })
      .then((response) => {
        const bookData = response.data;
        if (bookData.is_spoiler === 1) {
          window.alert(
            'このレビューはネタバレを含みます。望まない方は一つ前の画面へ戻ってください'
          );
        }
        setBookData(bookData); //書籍の情報を一個にまとめた
      })
      .catch((err) => {
        setErrorMessage(`エラー発生 ${err}`);
      })
      //「finally」は最後に必ず実行される処理群
      .finally(() => {
        setIsLoading(false);
      });
  }, [BookId]);

  return (
    <div className="detail-page">
      <Header />
      <h1 className="book_detail_h1">書籍の詳細</h1>
      <h2 className="error-massage">{errorMessage}</h2>
      {isLoading ? (
        <Loading />
      ) : (
        <div className="bookDetail">
          <p className="bookDetail__title">{bookData.title}</p>
          <h2 className="bookDetail__url">URL:</h2>
          <div className="bookDetail__url"><a href={bookData.url}>{bookData.url}</a></div>
          <h2 className="bookDetail__reviewer">レビュワー:</h2>
          <div className="bookDetail__reviewer">{bookData.reviewer}</div>
          <h2 className="bookDetail__detail">書籍の詳細情報:</h2>
          <div className="bookDetail__detail">{bookData.detail}</div>
          <h2 className="bookDetail__review">レビュー:</h2>
          <div className="bookDetail__review">{bookData.review}</div><br />
          {bookData.is_mine ? (
            <Link to={`/edit/${BookId}`} className="bookDetail__link-edit-book">
              書籍編集画面へ
            </Link>
          ) : (
            <></>
          )}
          <ReviewCommentInput BookId={BookId} />
        </div>
      )}
    </div>
  );
};