import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { useUrl } from '../../useUrl';
import { BookReviewInput } from '../book_review_input/BookReviewInput';
import { Header } from '../header/Header';
import './EditBookReview.scss';

export const EditBookReview = () => {
  const { BookId } = useParams(); //クエリパラメータを取得するには [] ではなく {} で囲わなければならない（ややこしい...）
  const navigate = useNavigate();
  const [cookies] = useCookies();
  const [bookTitle, setBookTitle] = useState('');
  const [bookUrl, setBookUrl] = useState('');
  const [bookDetail, setBookDetail] = useState('');
  const [bookReview, setBookReview] = useState('');
  const [isSpoiler, setIsSpoiler] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const editBookUrl = useUrl('bookDetailOperation', BookId); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const deleteBookUrl = useUrl('bookDetailOperation', BookId);
  const getBookDetailUrl = useUrl('bookDetailOperation', BookId);
  const [bookData, setBookData] = useState([]);

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  const editBook = () => {
    const data = {
      title: bookTitle,
      url: bookUrl,
      detail: bookDetail,
      review: bookReview,
      isSpoiler: isSpoiler
    };

    axios
      .put(editBookUrl, data, { headers })
      .then(() => {
        navigate('/');
      })
      .catch((error) => {
        setErrorMessage(`更新に失敗しました ${error}`);
      });
  };

  const deleteBook = () => {
    axios
      .delete(deleteBookUrl, { headers })
      .then(() => {
        navigate('/');
      })
      .catch((error) => {
        setErrorMessage(`削除に失敗しました ${error}`);
      });
  };

  useEffect(() => {
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

  return (
    <div className="edit_review">
      <Header />
      <h1>書籍レビュー編集</h1>
      <h2 className="error-massage">{errorMessage}</h2>
      <BookReviewInput
        bookData={bookData}
        isSpoiler={isSpoiler}
        setBookTitle={setBookTitle}
        setBookUrl={setBookUrl}
        setBookDetail={setBookDetail}
        setBookReview={setBookReview}
        setIsSpoiler={setIsSpoiler}
        BookOperations={editBook}
        deleteBook={deleteBook}
      />
    </div>
  );
};
