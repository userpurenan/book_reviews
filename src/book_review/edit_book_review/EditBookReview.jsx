import React, { useEffect, useState } from "react";

import { Header } from "../header/Header";
import axios from "axios";
import { url } from "../../const";
import { useCookies } from "react-cookie";
import { useNavigate, useParams } from "react-router-dom";
import { useSelector } from "react-redux";
import { BookReviewInput } from "../book_review_input/BookReviewInput";
import './EditBookReview.scss';


export const EditBookReview = () =>{
    const { BookId } = useParams(); //クエリパラメータを取得するには [] ではなく {} で囲わなければならない（ややこしい...）
    const navigate = useNavigate();
    const auth = useSelector((state) => state.auth.isSignIn);
    const [cookies] = useCookies();
    const [bookTitle, setBookTitle] = useState('');
    const [bookUrl, setBookUrl] = useState('');
    const [bookDetail, setBookDetail] = useState('');
    const [bookReview, setBookReview] = useState('');
    const [errorMessage, setErrorMessage] = useState('');
    const [bookData, setBookData] = useState({});

    const headers = {
        authorization: `Bearer ${cookies.token}`,
    };
  
    const editBook = () => {
        const data = {
            title: bookTitle,
            url: bookUrl,
            detail: bookDetail,
            review: bookReview
        }

        axios.put(`${url}/books/${BookId}`, data, { headers })
        .then(() =>{
            navigate('/');
        })
        .catch((err) =>{
            setErrorMessage(`更新に失敗しました ${err}`)
        })
    }

    const deleteBook = () => {
        axios.delete(`${url}/books/${BookId}`, { headers })
        .then(()=> {
            navigate('/');
        })
        .catch((err) => {
            setErrorMessage(`削除に失敗しました ${err}`)
        })
    }

    useEffect(() => {
        if(auth === false) return navigate('/login');

        axios.get(`${url}/books/${BookId}`, { headers })
        .then((res) => {
            if(!res.data.isMine) return navigate('/'); //自分の書いた書籍レビューじゃなかったらホーム画面に遷移する
            setBookData(res.data);
        })
        .catch((err) => {
            setErrorMessage(`ユーザー情報取得に失敗しました ${err}`);
        })
    },[]);

    return (
        <div>
            <Header />
            <h1>書籍レビュー編集</h1>
            <h2 className="error-massage">{errorMessage}</h2>
                <BookReviewInput 
                    bookData={bookData}
                    setBookTitle={setBookTitle}
                    setBookUrl={setBookUrl}
                    setBookDetail={setBookDetail}
                    setBookReview={setBookReview}
                    BookOperations={editBook}
                    deleteBook={deleteBook}
                />
        </div>
    )
}
