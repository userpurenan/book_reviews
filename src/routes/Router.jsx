import React from "react";
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { SignUp } from "../book_review/SignUp";
import { Login } from "../book_review/Login";
import { Home } from "../book_review/Home";
import { Test } from "../__test__/test";
import { EditProfile } from "../book_review/EditProfile";
import { CreateBookReview } from "../book_review/CreateBookReview";
import { BookReviewDetail } from "../book_review/BookReviewDetail";
import { EditBookReview } from "../book_review/EditBookReview";

export const Router = () => {

    return(
        <BrowserRouter>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/test" element={<Test />} />
                <Route path="/signup" element={<SignUp />} />
                <Route path="/" element={<Home />} />
                <Route path="/profile" element={<EditProfile />} />
                <Route path="/new" element={<CreateBookReview />} />
                <Route path="/detail/:BookId" element={<BookReviewDetail />} />
                <Route path="/edit/:BookId" element={<EditBookReview />} />
            </Routes>
        </BrowserRouter>
    )
}